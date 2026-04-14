export const getServicesData = (t: any) => [
  {
    icon: '🚗',
    featured: false,
    title: t('services.title.autoLoan'),
    desc: t('services.desc.autoLoan'),
    bgImage: './public/auto-loan.jpg',
    features: [
      t('services.feat.autoLoan.0'),
      t('services.feat.autoLoan.1'),
      t('services.feat.autoLoan.2'),
      t('services.feat.autoLoan.3'),
    ]
  },
  {
    icon: '📄',
    featured: false,
    title: t('services.title.coverage'),
    desc: t('services.desc.coverage'),
    bgImage: './public/coverage.jpg',
    features: [
      t('services.feat.coverage.0'),
      t('services.feat.coverage.1'),
      t('services.feat.coverage.2'),
    ]
  },
  {
    icon: '🛡️',
    featured: false,
    title: t('services.title.homeRefinance'),
    desc: t('services.desc.homeRefinance'),
    bgImage: './public/home-refinance.jpg',
    features: [
      t('services.feat.homeRefinance.0'),
      t('services.feat.homeRefinance.1'),
      t('services.feat.homeRefinance.2'),
      t('services.feat.homeRefinance.3'),
    ]
  },
  {
    icon: '📈',
    featured: false,
    title: t('services.title.credit'),
    desc: t('services.desc.credit'),
    bgImage: './public/credit.jpg',
    features: [
      t('services.feat.credit.0'),
      t('services.feat.credit.1'),
      t('services.feat.credit.2'),
    ]
  }
];
