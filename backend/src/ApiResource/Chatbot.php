<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\ChatbotController;
use App\Dto\ChatbotRequest;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/chatbot/query',
            controller: ChatbotController::class,
            input: ChatbotRequest::class,
            normalizationContext: ['groups' => ['chatbot:read']],
            denormalizationContext: ['groups' => ['chatbot:write']],
            name: 'chatbot_query'
        ),
    ],
    normalizationContext: ['groups' => ['chatbot:read']],
    denormalizationContext: ['groups' => ['chatbot:write']]
)]
class Chatbot
{
    
}