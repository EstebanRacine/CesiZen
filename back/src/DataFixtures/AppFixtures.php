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

        // Administrators
        foreach (['admin', 'admin2'] as $i => $username) {
            $user = new User();
            $user->setUsername($username);
            $user->setPassword(password_hash('admin'.$i, PASSWORD_BCRYPT));
            $user->setRoles(['ROLE_ADMIN']);
            $user->setActif(true);
            $user->setDateCreation(new \DateTime("-".($i + 3).' days'));
            $manager->persist($user);
            $this->addReference('admin_'.$i, $user);
        }

        // Catégories d'émotions
        $listeCategories = [
            ['Joie', '#FFD700'],      
            ['Colère', '#B22222'],    
            ['Peur', '#4B0082'],      
            ['Tristesse', '#1E90FF'],  
            ['Surprise', '#FFA500'],
            ['Dégoût', '#556B2F'],    
        ];
        foreach ($listeCategories as $i => [$nom, $couleur]) {
            $categorie = new CategorieEmotion();
            $categorie->setNom($nom);
            $categorie->setCouleur($couleur);
            $manager->persist($categorie);
            $this->addReference('categorie_'.$i, $categorie);
        }

        $emotionsData = [
            // Joie
            ['Fierté', '/uploads/emotions/dev/fierte.png', 0],
            ['Consentement', '/uploads/emotions/dev/consentement.png', 0],
            ['Enchantement', '/uploads/emotions/dev/enchantement.png', 0],
            ['Excitation', '/uploads/emotions/dev/excitation.png', 0],
            ['Émerveillement', '/uploads/emotions/dev/emerveillement.png', 0],
            ['Gratitude', '/uploads/emotions/dev/gratitude.png', 0],

            // Colère
            ['Frustration', '/uploads/emotions/dev/frustration.png', 1],
            ['Irritation', '/uploads/emotions/dev/irritation.png', 1],
            ['Rage', '/uploads/emotions/dev/rage.png', 1],
            ['Ressentiment', '/uploads/emotions/dev/ressentiment.png', 1],
            ['Agacement', '/uploads/emotions/dev/agacement.png', 1],
            ['Hostilité', '/uploads/emotions/dev/hostilite.png', 1],

            // Peur
            ['Inquiétude', '/uploads/emotions/dev/inquietude.png', 2],
            ['Anxiété', '/uploads/emotions/dev/anxiete.png', 2],
            ['Terreur', '/uploads/emotions/dev/terreur.png', 2],
            ['Appréhension', '/uploads/emotions/dev/apprehension.png', 2],
            ['Panique', '/uploads/emotions/dev/panique.png', 2],
            ['Crainte', '/uploads/emotions/dev/crainte.png', 2],

            // Tristesse
            ['Chagrin', '/uploads/emotions/dev/chagrin.png', 3],
            ['Mélancolie', '/uploads/emotions/dev/melancolie.png', 3],
            ['Abattement', '/uploads/emotions/dev/abattement.png', 3],
            ['Désespoir', '/uploads/emotions/dev/desespoir.png', 3],
            ['Solitude', '/uploads/emotions/dev/solitude.png', 3],
            ['Dépression', '/uploads/emotions/dev/depression.png', 3],

            // Surprise
            ['Étonnement', '/uploads/emotions/dev/etonnement.png', 4],
            ['Stupéfaction', '/uploads/emotions/dev/stupefaction.png', 4],
            ['Sidération', '/uploads/emotions/dev/sideration.png', 4],
            ['Incrédulité', '/uploads/emotions/dev/incredulite.png', 4],
            ['Confusion', '/uploads/emotions/dev/confusion.png', 4],

            // Dégoût
            ['Répulsion', '/uploads/emotions/dev/repulsion.png', 5],
            ['Déplaisir', '/uploads/emotions/dev/deplaisir.png', 5],
            ['Nausée', '/uploads/emotions/dev/nausee.png', 5],
            ['Dédain', '/uploads/emotions/dev/dedain.png', 5],
            ['Horreur', '/uploads/emotions/dev/horreur.png', 5],
            ['Dégoût profond', '/uploads/emotions/dev/degout_profond.png', 5],
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
        foreach ([['Actualités', 'NotepadText'], ['Santé', 'HeartPlus'], ['Stress', 'Brain']] as $i => [$nom, $icone]) {
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
