<?php

namespace App\Controller;

use App\Dto\ChatbotRequest;
use App\Service\ChatbotService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
class ChatbotController extends AbstractController
{
    private ChatbotService $chatbotService;
    private LoggerInterface $logger;

    /**
     * Constructor for ChatbotController.
     *
     * Sets up the controller with the chatbot service, serializer, validator, and logger.
     */
    public function __construct(
        ChatbotService $chatbotService,
        LoggerInterface $logger
    ) {
        $this->chatbotService = $chatbotService;
        $this->logger = $logger;
    }

    /**
     * Handle a chatbot query request.
     *
     * Takes the query from the request, gets campaign context if needed, generates a response using the service, and returns it as JSON.
     * If something goes wrong, it logs the error and returns an error message.
     *
     * @param ChatbotRequest $chatbotRequest The request with the query and optional campaign ID.
     * @return JsonResponse The response with the chatbot answer or an error.
     */
    #[Route('/chatbot/query', name: 'chatbot_query', methods: ['POST'])]
    public function query(ChatbotRequest $chatbotRequest): JsonResponse
    {
        try {
            $campaignContext = null;
            if ($chatbotRequest->campaignId) {
                $campaignContext = $this->chatbotService->getCampaignContext($chatbotRequest->campaignId);
                if (null === $campaignContext) {
                    return new JsonResponse(['error' => 'Campaign not found'], Response::HTTP_NOT_FOUND);
                }
            }

            $responseContent = $this->chatbotService->generateResponse(
                $chatbotRequest->query,
                $campaignContext
            );

            return new JsonResponse(['response' => $responseContent]);
        } catch (\Exception $e) {
            $this->logger->error('Chatbot query failed: ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => 'An unexpected error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}