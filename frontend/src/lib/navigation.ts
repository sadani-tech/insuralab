export type NavItem = {
  label: string;
  href: string;
  description: string;
};

/** Primary navigation — kept short on purpose. */
export const MAIN_NAV: NavItem[] = [
  { label: "Learn", href: "/learn", description: "Structured articles: general concepts first." },
  {
    label: "Protection",
    href: "/protection",
    description: "Risk categories: life, health, critical illness, disability, accident.",
  },
  {
    label: "Assessment",
    href: "/assessment",
    description: "Petakan topik proteksi yang relevan untuk dipelajari.",
  },
  {
    label: "Providers",
    href: "/providers",
    description: "Provider-agnostic case studies. Allianz is the first.",
  },
  {
    label: "Products",
    href: "/products",
    description: "Educational product records — source- and date-aware.",
  },
  {
    label: "Glossary",
    href: "/glossary",
    description: "Plain-language definitions of insurance terms.",
  },
  {
    label: "Explore",
    href: "/explore",
    description: "Visual exploration of how protection relates to risk.",
  },
];

/** Secondary navigation. */
export const SECONDARY_NAV: NavItem[] = [
  { label: "Quiz", href: "/quiz", description: "Low-stakes self-checks with explanations." },
  { label: "About", href: "/about", description: "Why InsuraLab exists." },
];

export const FOOTER_SECTIONS: { title: string; items: NavItem[] }[] = [
  {
    title: "Explore",
    items: [MAIN_NAV[0], MAIN_NAV[1], MAIN_NAV[2], MAIN_NAV[3], MAIN_NAV[4]].filter(
      Boolean,
    ) as NavItem[],
  },
  {
    title: "Resources",
    items: [
      MAIN_NAV[5]!,
      {
        label: "Methodology",
        href: "/methodology",
        description: "How InsuraLab organises knowledge.",
      },
    ],
  },
  {
    title: "Project",
    items: [
      SECONDARY_NAV[1]!,
      { label: "Disclaimer", href: "/disclaimer", description: "Educational use, not advice." },
    ],
  },
];
