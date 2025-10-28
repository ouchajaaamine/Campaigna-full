<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Controller\RoiController;
use App\Dto\RoiResponse;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(normalizationContext: ['groups' => ['campaign:read:full'], 'enable_max_depth' => true]),
        new Post(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['campaign:read']],
    denormalizationContext: ['groups' => ['campaign:write']]
)]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial'])]
class Campaign
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['campaign:read', 'campaign:read:full'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['campaign:read', 'campaign:write', 'campaign:read:full'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ["default" => "0.00"])]
    #[Groups(['campaign:read', 'campaign:write', 'campaign:read:full'])]
    private ?string $budget = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ["default" => "0.00"])]
    #[Groups(['campaign:read', 'campaign:read:full'])]
    private ?string $totalRevenue = '0.00';

    #[ORM\Column(type: Types::FLOAT, precision: 10, scale: 2, options: ["default" => "0.00"])]
    #[Groups(['campaign:read', 'campaign:read:full'])]
    private ?float $roiPercentage = 0.00;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['campaign:read', 'campaign:write', 'campaign:read:full'])]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['campaign:read', 'campaign:write', 'campaign:read:full'])]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(length: 255, options: ["default" => "draft"])]
    #[Groups(['campaign:read', 'campaign:write', 'campaign:read:full'])]
    private ?string $status = 'draft';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['campaign:read', 'campaign:read:full'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['campaign:read', 'campaign:read:full'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Metric>
     */
    #[ORM\OneToMany(mappedBy: 'campaign', targetEntity: Metric::class, orphanRemoval: true)]
    #[Groups(['campaign:read', 'campaign:read:full'])]
    #[MaxDepth(1)]
    private Collection $metrics;

    /**
     * @var Collection<int, Affiliate>
     */
    #[ORM\ManyToMany(targetEntity: Affiliate::class, inversedBy: 'campaigns')]
    #[Groups(['campaign:read', 'campaign:write', 'campaign:read:full'])]
    private Collection $affiliates;

    public function __construct()
    {
        $this->metrics = new ArrayCollection();
        $this->affiliates = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->calculateRevenueAndRoi();
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
        $this->calculateRevenueAndRoi();
    }

    /**
     * Calculate the total revenue and ROI for this campaign.
     *
     * It goes through all the metrics, adds up the revenue from each one.
     * If a metric doesn't have revenue but its name has "revenue" in it, it uses the value instead.
     * Then it sets the totalRevenue and figures out the ROI percentage based on the budget.
     */
    public function calculateRevenueAndRoi(): void
    {
        $totalRevenue = 0.0;
        foreach ($this->getMetrics() as $metric) {
            $metricRevenue = $metric->getRevenue();
            if ($metricRevenue !== null && (float) $metricRevenue > 0) {
                $totalRevenue += (float) $metricRevenue;
            } else {
                $name = $metric->getName() ?? '';
                if (stripos($name, 'revenue') !== false) {
                    $value = $metric->getValue();
                    if ($value !== null) {
                        $totalRevenue += (float) $value;
                    }
                }
            }
        }

        $this->totalRevenue = number_format($totalRevenue, 2, '.', '');

        $budget = (float) $this->getBudget();
        if ($budget > 0) {
            $roiPercentage = (($totalRevenue - $budget) / $budget) * 100;
            $this->setRoiPercentage(round($roiPercentage, 2));
        } else {
            $this->setRoiPercentage(0.00);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBudget(): ?string
    {
        return $this->budget;
    }

    public function setBudget(string $budget): static
    {
        $this->budget = $budget;

        return $this;
    }

    public function getTotalRevenue(): ?string
    {
        return $this->totalRevenue;
    }

    public function setTotalRevenue(string $totalRevenue): static
    {
        $this->totalRevenue = $totalRevenue;

        return $this;
    }

    public function getRoiPercentage(): ?float
    {
        return $this->roiPercentage;
    }

    public function setRoiPercentage(float $roiPercentage): static
    {
        $this->roiPercentage = $roiPercentage;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get the metrics for this campaign.
     *
     * Returns a Collection of Metric objects.
     *
     * @return Collection<int, Metric>|Metric[]
     */
    public function getMetrics(): Collection
    {
        return $this->metrics;
    }

    public function addMetric(Metric $metric): static
    {
        if (!$this->metrics->contains($metric)) {
            $this->metrics->add($metric);
            $metric->setCampaign($this);
        }

        return $this;
    }

    public function removeMetric(Metric $metric): static
    {
        if ($this->metrics->removeElement($metric)) {
            if ($metric->getCampaign() === $this) {
                $metric->setCampaign(null);
            }
        }

        return $this;
    }

    /**
     * Get the affiliates for this campaign.
     *
     * Returns a Collection of Affiliate objects.
     *
     * @return Collection<int, Affiliate>|Affiliate[]
     */
    public function getAffiliates(): Collection
    {
        return $this->affiliates;
    }

    public function addAffiliate(Affiliate $affiliate): static
    {
        if (!$this->affiliates->contains($affiliate)) {
            $this->affiliates->add($affiliate);
        }

        return $this;
    }

    public function removeAffiliate(Affiliate $affiliate): static
    {
        $this->affiliates->removeElement($affiliate);

        return $this;
    }


}
