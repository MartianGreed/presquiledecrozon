export interface RentalTestData {
  configuration: {
    rentalType: string;
    peopleCount: number;
    bedroomCount: number;
    bedrooms: Array<{
      singleBeds?: number;
      doubleBeds?: number;
      bunkBeds?: number;
    }>;
  };
  furnitures: {
    selected: string[];
    custom: string[];
  };
  description: {
    title: string;
    description: string;
  };
  address: {
    address: string;
    town: string;
  };
  preferences: {
    acceptedLastBooking: string;
    maxTimeBeforeBooking: string;
    beginBookingAt: string;
    endBookingAt: string;
  };
  taxes: {
    localTax: string;
    cleaningTax: number;
    linensTax: number;
  };
  prices: {
    dailyRate: number;
    weeklyRate: number;
    seasonalPrices: Array<{
      startDate: string;
      endDate: string;
      dailyRate: number;
      weeklyRate: number;
    }>;
  };
  conditions: {
    animalsAccepted: boolean;
    smokingAllowed: boolean;
    additionalRules: string[];
  };
}

export const VALID_RENTAL: RentalTestData = {
  configuration: {
    rentalType: 'Maison',
    peopleCount: 6,
    bedroomCount: 3,
    bedrooms: [
      { doubleBeds: 1 },
      { singleBeds: 2 },
      { doubleBeds: 1 }
    ]
  },
  furnitures: {
    selected: ['WiFi', 'Télévision', 'Lave-linge', 'Lave-vaisselle', 'Four', 'Micro-ondes'],
    custom: ['Barbecue Weber', 'Table de ping-pong', 'Chaise haute pour bébé']
  },
  description: {
    title: 'Charmante maison bretonne avec vue sur mer à Crozon',
    description: `Magnifique maison bretonne rénovée avec goût, idéalement située sur la presqu'île de Crozon.
    
Cette propriété offre une vue imprenable sur la mer d'Iroise et se trouve à seulement 500 mètres de la plage de Morgat.

La maison dispose de 3 chambres confortables, d'un grand salon lumineux avec cheminée, d'une cuisine entièrement équipée et d'une terrasse avec mobilier de jardin.

Le jardin clos de 800m² est parfait pour les familles avec enfants. Un parking privé pour 2 voitures est disponible.

Commerces et restaurants accessibles à pied en 5 minutes. Idéal pour des vacances en famille ou entre amis.`
  },
  address: {
    address: '12 Rue de la Plage',
    town: '(29160) Crozon'
  },
  preferences: {
    acceptedLastBooking: '3 jours',
    maxTimeBeforeBooking: '12 mois',
    beginBookingAt: 'Samedi',
    endBookingAt: 'Samedi'
  },
  taxes: {
    localTax: '1.2',
    cleaningTax: 60,
    linensTax: 20
  },
  prices: {
    dailyRate: 85,
    weeklyRate: 550,
    seasonalPrices: [
      {
        startDate: '15/06',
        endDate: '15/07',
        dailyRate: 120,
        weeklyRate: 750
      },
      {
        startDate: '16/07',
        endDate: '31/08',
        dailyRate: 150,
        weeklyRate: 950
      }
    ]
  },
  conditions: {
    animalsAccepted: true,
    smokingAllowed: false,
    additionalRules: [
      'Check-in entre 16h et 20h',
      'Check-out avant 10h',
      'Respect du voisinage après 22h',
      'Tri sélectif obligatoire'
    ]
  }
};

export const MINIMAL_RENTAL: RentalTestData = {
  configuration: {
    rentalType: 'Appartement',
    peopleCount: 2,
    bedroomCount: 1,
    bedrooms: [
      { doubleBeds: 1 }
    ]
  },
  furnitures: {
    selected: ['WiFi'],
    custom: []
  },
  description: {
    title: 'Studio cosy proche plage',
    description: 'Petit studio fonctionnel et bien équipé, parfait pour un couple.'
  },
  address: {
    address: '5 Avenue de la Mer',
    town: '(29570) Camaret-sur-Mer'
  },
  preferences: {
    acceptedLastBooking: '1 jour',
    maxTimeBeforeBooking: '6 mois',
    beginBookingAt: 'Flexible',
    endBookingAt: 'Flexible'
  },
  taxes: {
    localTax: '1.0',
    cleaningTax: 30,
    linensTax: 10
  },
  prices: {
    dailyRate: 45,
    weeklyRate: 280,
    seasonalPrices: []
  },
  conditions: {
    animalsAccepted: false,
    smokingAllowed: false,
    additionalRules: []
  }
};

export function generateUniqueRentalTitle(): string {
  const timestamp = Date.now();
  return `Test Rental ${timestamp} - Villa avec piscine`;
}

export function getTestImagePaths(): string[] {
  return [
    'tests/playwright/fixtures/test-images/cover.jpg',
    'tests/playwright/fixtures/test-images/1.jpg',
    'tests/playwright/fixtures/test-images/2.jpg',
    'tests/playwright/fixtures/test-images/3.jpg',
    'tests/playwright/fixtures/test-images/4.jpg',
    'tests/playwright/fixtures/test-images/5.jpg'
  ];
}
