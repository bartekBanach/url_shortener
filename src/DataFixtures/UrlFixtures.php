<?php
/**
 * URL fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Url;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class UrlFixtures.
 */
class UrlFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * List of URL addresses.
     *
     * @var string[]
     */
    private array $urlsList = [
        'https://www.example.com',
        'https://www.wikipedia.org',
        'https://www.google.com',
        'https://www.github.com',
        'https://www.stackoverflow.com',
        'https://www.reddit.com',
        'https://www.twitter.com',
        'https://www.linkedin.com',
        'https://www.medium.com',
        'https://www.quora.com',
    ];

    /**
     * Load data.
     */
    protected function loadData(): void
    {
        $this->createMany(20, 'urls', function (int $i) {
            $url = new Url();
            $url->setLongUrl($this->urlsList[array_rand($this->urlsList)]); // Select a random URL from the list
            $url->setShortUrl($this->faker->lexify('??????')); // Generates a random 6-character short URL code
            $url->setCreatedAt(
                \DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-100 days', '-1 days'))
            );

            // Assign random tags to the URL
            $randomTags = $this->getRandomReferences('tags', mt_rand(1, 3)); // Get 1 to 3 random tags
            foreach ($randomTags as $tag) {
                $url->addTag($tag);
            }

            /** @var User $author */
            $author = $this->getRandomReference('users');
            $url->setAuthor($author);

            return $url;
        });

        $this->manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: CategoryFixtures::class, 1: TagFixtures::class, 2: UserFixtures::class}
     */
    public function getDependencies(): array
    {
        return [TagFixtures::class, UserFixtures::class];
    }
}
