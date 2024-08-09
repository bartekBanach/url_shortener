<?php
/**
 * Tag fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Tag;

/**
 * Class TagFixtures.
 */
class TagFixtures extends AbstractBaseFixtures
{
    /**
     * List of tag titles.
     *
     * @var string[]
     */
    private array $tagTitles = [
        'Technology',
        'Science',
        'Health',
        'Education',
        'Entertainment',
        'Sports',
        'Politics',
        'Business',
        'Travel',
        'Food',
    ];

    /**
     * Load data.
     */
    protected function loadData(): void
    {
        for ($i = 0; $i < 10; ++$i) {
            $tag = new Tag();
            $title = $this->tagTitles[array_rand($this->tagTitles)];
            $tag->setTitle($title);

            // Generate a random createdAt date
            $createdAt = \DateTimeImmutable::createFromMutable(
                $this->faker->dateTimeBetween('-1 year', 'now')
            );

            // Generate a random updatedAt date, ensuring it's after createdAt
            $updatedAt = \DateTimeImmutable::createFromMutable(
                $this->faker->dateTimeBetween($createdAt->format('Y-m-d'), 'now')
            );

            $tag->setCreatedAt($createdAt);
            $tag->setUpdatedAt($updatedAt);

            $this->manager->persist($tag);
        }
        $this->manager->flush();
    }
}
