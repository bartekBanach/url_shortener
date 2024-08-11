<?php
/**
 * Url entity.
 */

namespace App\Entity;

use App\Repository\UrlRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Url.
 */
#[ORM\Entity(repositoryClass: UrlRepository::class)]
#[ORM\Table(name: 'urls')]
class Url
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Long URL.
     *
     * @var string|null the original long URL
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Url]
    private ?string $longUrl = null;

    /**
     * Short URL.
     *
     * @var string|null the short URL code
     */
    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Type('string')]
    #[Assert\Length(min: 1, max: 30)]
    private ?string $shortUrl = null;

    /**
     * Created at.
     *
     * @psalm-suppress PropertyNotSetInConstructor
     */
    #[ORM\Column]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Tags.
     *
     * @var ArrayCollection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\JoinTable(name: 'tasks_tags')]
    private Collection $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * Getter tfor ID.
     *
     * @return int|null the identifier of the URL entity
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for long URL.
     *
     * @return string|null the original long URL
     */
    public function getLongUrl(): ?string
    {
        return $this->longUrl;
    }

    /**
     * Setter for long URL.
     *
     * @param string $longUrl the original long URL
     */
    public function setLongUrl(string $longUrl): static
    {
        $this->longUrl = $longUrl;

        return $this;
    }

    /**
     * Getter for short URL.
     *
     * @return string|null the short URL code
     */
    public function getShortUrl(): ?string
    {
        return $this->shortUrl;
    }

    /**
     * Setter for short URL.
     *
     * @param string|null $shortUrl the short URL code
     */
    public function setShortUrl(?string $shortUrl): static
    {
        $this->shortUrl = $shortUrl;

        return $this;
    }

    /**
     * Getter for createdAt.
     *
     * @return \DateTimeImmutable|null the date and time when the URL was created
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Setter for createdAt.
     *
     * @param \DateTimeImmutable $createdAt the date and time when the URL was created
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Getter for tags.
     *
     * @return Collection<int, Tag> Tags collection
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * Add tag.
     *
     * @param Tag $tag Tag entity
     */
    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    /**
     * Remove tag.
     *
     * @param Tag $tag Tag entity
     */
    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }
}
