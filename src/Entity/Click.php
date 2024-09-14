<?php
/**
 * Click entity.
 */

namespace App\Entity;

use App\Repository\ClickRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents a click event associated with a shortened URL.
 */
#[ORM\Table(name: 'clicks')]
#[ORM\Entity(repositoryClass: ClickRepository::class)]
class Click
{
    /**
     * Primary key for the click entity.
     *
     * @var int|null The unique identifier of the click
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The date and time when the click was created.
     *
     * @var \DateTimeImmutable|null The creation timestamp of the click
     */
    #[ORM\Column]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * The IP address from which the click originated.
     *
     * @var string|null The IP address of the clicker
     */
    #[ORM\Column(length: 255)]
    private ?string $ipAddress = null;

    /**
     * The user agent string of the clicker.
     *
     * @var string|null The user agent string of the clicker
     */
    #[ORM\Column(type: Types::TEXT)]
    private ?string $userAgent = null;

    /**
     * The URL associated with the click.
     *
     * @var Url|null The URL entity that this click is associated with
     */
    #[ORM\ManyToOne(targetEntity: Url::class, inversedBy: 'clicks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Url $url = null;

    /**
     * Getter for the unique identifier of the click.
     *
     * @return int|null The unique identifier of the click
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for the creation timestamp of the click.
     *
     * @return \DateTimeImmutable|null The creation timestamp of the click
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Setter for the creation timestamp of the click.
     *
     * @param \DateTimeImmutable $createdAt The creation timestamp of the click
     *
     * @return static This instance for method chaining
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Getter for the IP address of the clicker.
     *
     * @return string|null The IP address of the clicker
     */
    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    /**
     * Setter for the IP address of the clicker.
     *
     * @param string $ipAddress The IP address of the clicker
     *
     * @return static This instance for method chaining
     */
    public function setIpAddress(string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * Getter for the user agent string of the clicker.
     *
     * @return string|null The user agent string of the clicker
     */
    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    /**
     * Setter for the user agent string of the clicker.
     *
     * @param string $userAgent The user agent string of the clicker
     *
     * @return static This instance for method chaining
     */
    public function setUserAgent(string $userAgent): static
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * Getter for the URL associated with the click.
     *
     * @return Url|null The URL entity that this click is associated with
     */
    public function getUrl(): ?Url
    {
        return $this->url;
    }

    /**
     * Setter for the URL associated with the click.
     *
     * @param Url|null $url The URL entity that this click is associated with
     *
     * @return static This instance for method chaining
     */
    public function setUrl(?Url $url): static
    {
        $this->url = $url;

        return $this;
    }
}
