// Mock data pour tester l'interface d'administration des infos
export const mockInfos = [
  {
    id: 1,
    titre: "Guide de Méditation Pleine Conscience",
    contenu: "La méditation pleine conscience est une pratique qui consiste à porter son attention sur l'instant présent, sans jugement. Cette pratique peut considérablement réduire le stress et améliorer votre bien-être mental. Voici un guide complet pour commencer votre voyage vers la pleine conscience...",
    image: "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=250&fit=crop",
    dateCreation: "2024-01-15T10:30:00Z",
    menu: {
      id: 1,
      nom: "Méditation"
    }
  },
  {
    id: 2,
    titre: "Recettes Healthy pour l'Été",
    contenu: "L'été arrive et il est temps de rafraîchir votre alimentation ! Découvrez nos meilleures recettes saines et légères qui vous donneront de l'énergie tout en vous aidant à rester en forme. Des salades colorées aux smoothies vitaminés, tout y est...",
    image: "https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=250&fit=crop",
    dateCreation: "2024-01-10T14:20:00Z",
    menu: {
      id: 2,
      nom: "Nutrition"
    }
  },
  {
    id: 3,
    titre: "5 Exercices Efficaces pour le Bureau",
    contenu: "Passer de longues heures assis au bureau peut créer des tensions musculaires et affecter votre posture. Voici 5 exercices simples que vous pouvez faire directement à votre poste de travail pour soulager vos muscles et améliorer votre bien-être...",
    image: "https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=250&fit=crop",
    dateCreation: "2024-01-08T09:15:00Z",
    menu: {
      id: 3,
      nom: "Sport"
    }
  },
  {
    id: 4,
    titre: "Techniques de Respiration Anti-Stress",
    contenu: "Le stress fait partie de notre quotidien, mais nous pouvons apprendre à le gérer efficacement grâce à des techniques de respiration simples. Ces exercices peuvent être pratiqués n'importe où et ne prennent que quelques minutes...",
    image: "https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=400&h=250&fit=crop",
    dateCreation: "2024-01-05T16:45:00Z",
    menu: {
      id: 1,
      nom: "Méditation"
    }
  },
  {
    id: 5,
    titre: "Planifier ses Repas de la Semaine",
    contenu: "La planification des repas est une excellente stratégie pour maintenir une alimentation équilibrée tout en économisant du temps et de l'argent. Découvrez nos conseils pour organiser efficacement vos menus hebdomadaires et gagner en sérénité...",
    image: "https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=400&h=250&fit=crop",
    dateCreation: "2024-01-02T11:30:00Z",
    menu: {
      id: 2,
      nom: "Nutrition"
    }
  }
];

export const mockMenus = [
  { id: 1, nom: "Méditation" },
  { id: 2, nom: "Nutrition" },
  { id: 3, nom: "Sport" },
  { id: 4, nom: "Sommeil" },
  { id: 5, nom: "Bien-être" }
];
