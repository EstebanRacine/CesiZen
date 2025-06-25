<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\CategorieEmotion;
use App\Entity\Emotion;
use App\Entity\Menu;
use App\Entity\Info;
use App\Entity\Tracker;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Users
        foreach (['alice', 'bob', 'carol'] as $i => $username) {
            $user = new User();
            $user->setUsername($username);
            $user->setPassword(password_hash('password'.$i, PASSWORD_BCRYPT));
            $user->setRoles(['ROLE_USER']);
            $user->setActif(true);
            $user->setDateCreation(new \DateTime("-$i days"));
            $manager->persist($user);
            $this->addReference('user_'.$i, $user);
        }

        // Catégories d'émotions
        foreach ([['Positive', '#00FF00'], ['Négative', '#FF0000'], ['Neutre', '#CCCCCC']] as $i => [$nom, $couleur]) {
            $categorie = new CategorieEmotion();
            $categorie->setNom($nom);
            $categorie->setCouleur($couleur);
            $manager->persist($categorie);
            $this->addReference('categorie_'.$i, $categorie);
        }

        // Émotions
        $emotionsData = [
            ['Joie', 'smile', 0],
            ['Tristesse', 'sad', 1],
            ['Colère', 'angry', 1],
            ['Sérénité', 'relaxed', 2],
            ['Surprise', 'surprised', 2],
        ];
        foreach ($emotionsData as $i => [$nom, $icone, $catIdx]) {
            $emotion = new Emotion();
            $emotion->setNom($nom);
            $emotion->setIcone($icone);
            $emotion->setActif(true);
            $emotion->setDateCreation(new \DateTime("-$i days"));
            $emotion->setDernierModificateur($this->getReference('user_'.($i % 3), User::class));
            $emotion->setCategorie($this->getReference('categorie_'.$catIdx, CategorieEmotion::class));
            $manager->persist($emotion);
            $this->addReference('emotion_'.$i, $emotion);
        }

        // Menus
        foreach ([['Accueil', 'home'], ['Profil', 'user'], ['Statistiques', 'chart']] as $i => [$nom, $icone]) {
            $menu = new Menu();
            $menu->setNom($nom);
            $menu->setIcone($icone);
            $menu->setActif(true);
            $menu->setDateCreation(new \DateTime("-$i days"));
            $menu->setDernierModificateur($this->getReference('user_'.($i % 3), User::class));
            $manager->persist($menu);
            $this->addReference('menu_'.$i, $menu);
        }

        // Infos
        foreach ([['Bienvenue', 'Bienvenue sur CesiZen !', 0], ['Astuce', 'Pensez à respirer !', 1], ['Info', 'Nouvelle fonctionnalité disponible.', 2]] as $i => [$titre, $contenu, $menuIdx]) {
            $info = new Info();
            $info->setTitre($titre);
            $info->setContenu($contenu);
            $info->setActif(true);
            $info->setDateCreation(new \DateTime("-$i days"));
            $info->setCreateur($this->getReference('user_'.($i % 3), User::class));
            $info->setMenu($this->getReference('menu_'.$menuIdx, Menu::class));
            $manager->persist($info);
            $this->addReference('info_'.$i, $info);
        }

        // Trackers
        for ($i = 0; $i < 6; $i++) {
            $tracker = new Tracker();
            $tracker->setUser($this->getReference('user_'.($i % 3), User::class));
            $tracker->setEmotion($this->getReference('emotion_'.($i % 5), Emotion::class));
            $tracker->setDatetime(new \DateTime("-$i hours"));
            $tracker->setCommentaire("Commentaire $i");
            $tracker->setActif(true);
            $manager->persist($tracker);
            $this->addReference('tracker_'.$i, $tracker);
        }

        $manager->flush();
    }
}
