<?php
/**
 * URL fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Url;

/**
 * Class UrlFixtures.
 */
class UrlFixtures extends AbstractBaseFixtures
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

        for ($i = 0; $i < 10; ++$i) {
            $url = new Url();
            $url->setLongUrl($this->urlsList[array_rand($this->urlsList)]); // Select a random URL from the list
            $url->setShortUrl($this->faker->lexify('??????')); // Generates a random 6-character short URL code
            $url->setCreatedAt(
                \DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-100 days', '-1 days'))
            );
            $this->manager->persist($url);
        }

        $this->manager->flush();
    }
}
