<?php
/**
 * AppFixtures class.
 *
 */

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Loads application fixtures for the database.
 */
class AppFixtures extends Fixture
{
    /**
     * Loads the fixtures into the database.
     *
     * @param ObjectManager $manager The object manager
     *
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
