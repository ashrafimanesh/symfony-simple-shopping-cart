<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;

class AppFixtures extends Fixture
{
	private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
    	$this->persistAdmin($manager);

        $this->persistCustomers($manager);

        $manager->flush();
    }

    protected function persistAdmin(ObjectManager $manager)
    {
		$user = new User();
        $user->setUsername('admin');
        $password = $this->encoder->encodePassword($user, '1234');
        $user->setPassword($password);
        $user->setFirstName('Administrator');
        $user->setRoles([User::ROLE_ADMIN]);
        $user->setCreatedAt(new \DateTime());
        $manager->persist($user);
    }

    protected function persistCustomers(ObjectManager $manager)
    {
		// create 20 customers!
        for ($i = 1; $i <= 20; $i++) {
			$user = new User();
	        $user->setUsername('user'.$i);
	        $password = $this->encoder->encodePassword($user, '1234');
	        $user->setPassword($password);
	        $user->setFirstName('Customer '.$i);
	        $user->setCreatedAt(new \DateTime());
            $manager->persist($user);
        }    	
    }
}
