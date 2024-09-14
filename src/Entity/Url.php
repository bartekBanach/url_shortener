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

    /**
     * Author.
     *
     * @var User|null The author of the URL
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?User $author = null;

    /**
     * Clicks associated with the URL.
     *
     * @var Collection<int, Click>
     */
    #[ORM\OneToMany(mappedBy: 'url', targetEntity: Click::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $clicks;

    /**
     * Url constructor.
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->clicks = new ArrayCollection();
    }

    /**
     * Getter for ID.
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
     *
     * @return static this instance for method chaining
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
     *
     * @return static this instance for method chaining
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
     *
     * @return static this instance for method chaining
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
     *
     * @return static this instance for method chaining
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
     *
     * @return static this instance for method chaining
     */
    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * Convert entity to string.
     *
     * @return string the short URL code or long URL, or 'N/A' if not set
     */
    public function __toString(): string
    {
        return $this->shortUrl ?? $this->longUrl ?? 'N/A';
    }

    /**
     * Getter for author.
     *
     * @return User|null The author of the URL
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Setter for author.
     *
     * @param User|null $author The author of the URL
     *
     * @return static this instance for method chaining
     */
    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Getter for clicks.
     *
     * @return Collection<int, Click> Clicks collection
     */
    public function getClicks(): Collection
    {
        return $this->clicks;
    }

    /**
     * Add click.
     *
     * @param Click $click Click entity
     *
     * @return static this instance for method chaining
     */
    public function addClick(Click $click): static
    {
        if (!$this->clicks->contains($click)) {
            $this->clicks->add($click);
            $click->setUrl($this);  // Set the owning side
        }

        return $this;
    }

    /**
     * Remove click.
     *
     * @param Click $click Click entity
     *
     * @return static this instance for method chaining
     */
    public function removeClick(Click $click): static
    {
        if ($this->clicks->removeElement($click)) {
            // Set the owning side to null (unless already changed)
            if ($click->getUrl() === $this) {
                $click->setUrl(null);
            }
        }

        return $this;
    }
}
