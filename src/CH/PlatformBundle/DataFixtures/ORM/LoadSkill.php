<?php

namespace CH\PlatformBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use CH\PlatformBundle\Entity\Skill;

class LoadSkill implements FixtureInterface
{
  public function load(ObjectManager $manager)
  {
    // Liste des noms de compétences à ajouter
    $names = array('Précision de tir', 'Puissance de tir', 'Effet', 'Précision de passe', 'Dribble', 'Vitesse', 'Accélération');

    foreach ($names as $name) {
      // On crée la compétence
      $skill = new Skill();
      $skill->setName($name);

      // On la persiste
      $manager->persist($skill);
    }

    // On déclenche l'enregistrement de toutes les catégories
    $manager->flush();
  }
}