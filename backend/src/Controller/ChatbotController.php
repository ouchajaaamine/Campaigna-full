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
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;

    public function __construct(
        ChatbotService $chatbotService,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ) {
        $this->chatbotService = $chatbotService;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->logger = $logger;
    }

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