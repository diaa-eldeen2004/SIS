// Simple i18n scaffolding
export const translations = {
  en: {
    welcome: "Welcome",
    dashboard: "Dashboard",
    help: "Help & Support",
    notFound: "Page Not Found"
  },
  ar: {
    welcome: "مرحبا",
    dashboard: "لوحة التحكم",
    help: "المساعدة والدعم",
    notFound: "الصفحة غير موجودة"
  }
};

export function t(key, lang = 'en') {
  return translations[lang] && translations[lang][key] ? translations[lang][key] : key;
}
