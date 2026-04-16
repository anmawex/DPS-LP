import { CarFront, ClipboardCheck, Home, BarChart3 } from 'lucide-astro';

export const getServicesData = (t: any) => [
  {
    icon: CarFront,
    featured: false,
    title: t('services.title.autoLoan'),
    desc: t('services.desc.autoLoan'),
    bgImage: '/auto-loan.jpg',
    features: [
      t('services.feat.autoLoan.0'),
      t('services.feat.autoLoan.1'),
      t('services.feat.autoLoan.2'),
      t('services.feat.autoLoan.3'),
    ]
  },
  {
    icon: ClipboardCheck,
    featured: false,
    title: t('services.title.coverage'),
    desc: t('services.desc.coverage'),
    bgImage: '/coverage.jpg',
    features: [
      t('services.feat.coverage.0'),
      t('services.feat.coverage.1'),
      t('services.feat.coverage.2'),
    ]
  },
  {
    icon: Home,
    featured: false,
    title: t('services.title.homeRefinance'),
    desc: t('services.desc.homeRefinance'),
    bgImage: '/home-refinance.jpg',
    features: [
      t('services.feat.homeRefinance.0'),
      t('services.feat.homeRefinance.1'),
      t('services.feat.homeRefinance.2'),
      t('services.feat.homeRefinance.3'),
    ]
  },
  {
    icon: BarChart3,
    featured: false,
    title: t('services.title.credit'),
    desc: t('services.desc.credit'),
    bgImage: '/credit.jpg',
    features: [
      t('services.feat.credit.0'),
      t('services.feat.credit.1'),
      t('services.feat.credit.2'),
    ]
  }
];
