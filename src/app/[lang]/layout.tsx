import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { Cairo, Poppins } from "next/font/google";
import "../globals.css";
import { dirOf, hasLocale, locales } from "@/lib/i18n";
import { getDictionary } from "@/lib/dictionaries";
import { Providers } from "@/components/providers";
import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import { ChatBot } from "@/components/chat/ChatBot";
import "@/components/chat/chat.css";
import { getProducts } from "@/lib/products";
import { getSettings, publicSettings } from "@/lib/settings";

const poppins = Poppins({ subsets: ["latin"], weight: ["400", "500", "600", "700"], variable: "--font-poppins" });
const cairo = Cairo({ subsets: ["arabic", "latin"], weight: ["400", "600", "700", "800"], variable: "--font-cairo" });

export const generateStaticParams = () => locales.map((lang) => ({ lang }));

export async function generateMetadata({ params }: LayoutProps<"/[lang]">): Promise<Metadata> {
  const { lang } = await params;
  if (!hasLocale(lang)) return {};
  const d = getDictionary(lang);
  return {
    title: { default: d.meta.title, template: "%s · SOFT4DZ" },
    description: d.meta.description,
    icons: { icon: "/logo.png" },
    alternates: { languages: { fr: "/fr", ar: "/ar", en: "/en" } },
  };
}

export default async function RootLayout({ children, params }: LayoutProps<"/[lang]">) {
  const { lang } = await params;
  if (!hasLocale(lang)) notFound();
  const dict = getDictionary(lang);
  const [products, settings] = await Promise.all([getProducts(), getSettings()]);

  return (
    <html lang={lang} dir={dirOf(lang)} className={`${poppins.variable} ${cairo.variable}`}>
      <body>
        <Providers locale={lang} dict={dict} products={products} settings={publicSettings(settings)}>
          <Header />
          <main className="w">{children}</main>
          <Footer />
          <ChatBot />
        </Providers>
      </body>
    </html>
  );
}
