<?php

namespace App\Controller;


use App\Entity\Campaign;
use App\Repository\CampaignRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class RoiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CampaignRepository $campaignRepository
    ) {}

    public function __invoke(int $id)
    {
        
        $campaign = $this->entityManager->getRepository(Campaign::class)->find($id);
        
        if (!$campaign) {
            return new JsonResponse(['message' => 'Campaign with ID ' . $id . ' not found.'], JsonResponse::HTTP_NOT_FOUND);
        }


        $totalSpent = $campaign->getBudget();
        
        $totalRevenue = $this->campaignRepository->getTotalRevenueByCampaignId($id);

        $roi = $totalSpent > 0 ? (($totalRevenue - $totalSpent) / $totalSpent) * 100 : 0;

        return new JsonResponse(
            [
                'campaignId' => $id,
                'campaignName' => $campaign->getName(),
                'budget' => round($totalSpent, 2),
                'totalRevenue' => round($totalRevenue, 2),
                'roiPercentage' => round($roi, 2),
                'status' => $roi > 0 ? 'profitable' : 'loss',
                'calculatedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
            ]
        );
    }
}