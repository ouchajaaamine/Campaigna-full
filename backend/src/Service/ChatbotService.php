<?php

namespace App\Service;

use App\Entity\Campaign;
use App\Repository\CampaignRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class ChatbotService
{
    private HttpClientInterface $httpClient;
    private string $openRouterToken;
    private EntityManagerInterface $entityManager;
    private CacheInterface $cache;
    private LoggerInterface $logger;
    private const OPENROUTER_MODEL = 'openai/gpt-4o-mini';

    /**
     * Constructor for ChatbotService.
     *
     * Sets up the service with things it needs like HTTP client, database manager, cache, and logger.
     */
    public function __construct(
        HttpClientInterface $httpClient,
        ParameterBagInterface $parameterBag,
        EntityManagerInterface $entityManager,
        CacheInterface $cache,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->openRouterToken = $parameterBag->get('openrouter_token');
        $this->entityManager = $entityManager;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * Generate a response to a user's query.
     *
     * It uses cache to avoid repeating work, builds a prompt, calls the AI API, and if that fails, gives a fallback answer.
     *
     * @param string $query The question from the user.
     * @param array|null $campaignContext Info about the campaign, if any.
     * @return string The response text.
     */
    public function generateResponse(string $query, ?array $campaignContext = null): string
    {
        $campaignId = $campaignContext['campaign_id'] ?? null;
        $cacheKey = 'chatbot_response_' . md5($query . ($campaignId ?? ''));

        try {
            return $this->cache->get($cacheKey, function() use ($query, $campaignContext) {
                try {
                    $prompt = $this->buildPrompt($query, $campaignContext);
                    return $this->callOpenRouterAPI($prompt);
                } catch (\Exception $e) {
                    return $this->generateFallbackResponse($query, $campaignContext);
                }
            });
        } catch (\Exception $e) {
            return $this->generateFallbackResponse($query, $campaignContext);
        }
    }

    /**
     * Build the prompt to send to the AI.
     *
     * It starts with a base message about being an assistant, adds campaign data if given, and includes the user's query.
     *
     * @param string $query The user's question.
     * @param array|null $campaignContext Campaign details.
     * @return string The full prompt.
     */
    private function buildPrompt(string $query, ?array $campaignContext = null): string
    {
        $basePrompt = "You are an AI assistant specializing in advertising campaign management and ROI analysis. ";

        if ($campaignContext) {
            $basePrompt .= "You have access to the following campaign data:\\n";
            $basePrompt .= json_encode($campaignContext, JSON_PRETTY_PRINT) . "\\n\\n";
            $basePrompt .= "Use this data to provide personalized, data-driven advice.\\n\\n";
        }

        $basePrompt .= "User query: {$query}\\n\\n";
        $basePrompt .= "Please provide a helpful, professional response:";

        return $basePrompt;
    }

    /**
     * Call the OpenRouter API to get AI response.
     *
     * Sends a POST request with the prompt and gets back the AI's answer. If something goes wrong, it throws an error.
     *
     * @param string $prompt The message to send to AI.
     * @return string The AI's response.
     * @throws \Exception If the API call fails.
     */
    private function callOpenRouterAPI(string $prompt): string
    {
        $url = "https://openrouter.ai/api/v1/chat/completions";

        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->openRouterToken,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => 'https://campaigna.com',
                'X-Title' => 'Campaigna Chatbot',
            ],
            'json' => [
                'model' => self::OPENROUTER_MODEL,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7,
                'top_p' => 0.9,
            ],
            'timeout' => 30,
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            $errorData = $response->toArray(false);
            $errorMessage = $errorData['error']['message'] ?? "OpenRouter API returned status code: {$statusCode}";
            // Log the full error response for debugging
            $this->logger->error('OpenRouter API Error: ' . $errorMessage, ['response' => $errorData]);
            throw new \Exception($errorMessage);
        }

        $data = $response->toArray();

        if (isset($data['choices'][0]['message']['content'])) {
            return trim($data['choices'][0]['message']['content']);
        } else {
            throw new \Exception('Unexpected response format from OpenRouter API');
        }
    }

    /**
     * Get context info for a campaign.
     *
     * Fetches the campaign from database, calculates totals from metrics, and returns an array with campaign details and metrics summary.
     *
     * @param int $campaignId The ID of the campaign.
     * @return array|null The context data, or null if campaign not found.
     */
    public function getCampaignContext(int $campaignId): ?array
    {
        $cacheKey = "chatbot_campaign_{$campaignId}";

        try {
            return $this->cache->get($cacheKey, function() use ($campaignId) {

                $campaign = $this->entityManager->getRepository(Campaign::class)->find($campaignId);

                if (!$campaign) {
                    return null;
                }

                $metrics = $campaign->getMetrics();
                $totalClicks = 0;
                $totalConversions = 0;
                $totalRevenue = 0.0;
                $totalSpent = 0.0;

                foreach ($metrics as $metric) {
                    $metricName = strtolower($metric->getName());
                    $metricValue = (float) $metric->getValue();

                    if (strpos($metricName, 'views') !== false || strpos($metricName, 'searches') !== false || strpos($metricName, 'clicks') !== false || strpos($metricName, 'impressions') !== false) {
                        $totalClicks += $metricValue;
                    } elseif (strpos($metricName, 'sales') !== false || strpos($metricName, 'orders') !== false || strpos($metricName, 'conversions') !== false || strpos($metricName, 'purchases') !== false) {
                        $totalConversions += $metricValue;
                    } elseif (strpos($metricName, 'revenue') !== false || strpos($metricName, 'income') !== false) {
                        $totalRevenue += $metricValue;
                    } elseif (strpos($metricName, 'cost') !== false || strpos($metricName, 'spend') !== false || strpos($metricName, 'spent') !== false) {
                        $totalSpent += $metricValue;
                    }
                }

                $roi = $totalSpent > 0 ? (($totalRevenue - $totalSpent) / $totalSpent) * 100 : 0;

                $affiliateNames = [];
                foreach ($campaign->getAffiliates() as $affiliate) {
                    $affiliateNames[] = $affiliate->getName();
                }

                $context = [
                    'campaign_id' => $campaign->getId(),
                    'name' => $campaign->getName(),
                    'budget' => $campaign->getBudget(),
                    'status' => $campaign->getStatus(),
                    'affiliates' => $affiliateNames,
                    'current_metrics' => [
                        'clicks' => $totalClicks,
                        'conversions' => $totalConversions,
                        'revenue' => $totalRevenue,
                        'cost' => $totalSpent,
                    ],
                    'roi_calculation' => [
                        'total_spent' => $totalSpent,
                        'total_revenue' => $totalRevenue,
                        'roi_percentage' => round($roi, 2),
                        'status' => $roi >= 0 ? 'profit' : 'loss',
                    ],
                ];

                return $context;
            });

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Generate a fallback response when AI fails.
     *
     * If there's campaign context, it makes a message with campaign info. Otherwise, a general sorry message.
     *
     * @param string $query The original query.
     * @param array|null $campaignContext Campaign data.
     * @return string The fallback response.
     */
    private function generateFallbackResponse(string $query, ?array $campaignContext = null): string
    {
        if ($campaignContext) {
            $campaignName = $campaignContext['name'] ?? 'your campaign';
            $budget = $campaignContext['budget'] ?? 0;
            $metrics = $campaignContext['current_metrics'] ?? [];
            $roi = $campaignContext['roi_calculation']['roi_percentage'] ?? 0;
            
            return "I apologize, but I'm currently unable to access the AI service. However, based on your **{$campaignName}** campaign data (Budget: $" . number_format($budget, 2) . ", ROI: " . number_format($roi, 2) . "%), I can see you have active performance metrics. Please try your question again in a moment, or contact support if the issue persists.";
        }
        
        return "I apologize, but I'm currently unable to process your request due to a technical issue. Please try again in a moment.";
    }
}