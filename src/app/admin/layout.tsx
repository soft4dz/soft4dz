import type { Metadata } from "next";
import { Poppins } from "next/font/google";
import "../globals.css";
import "./admin.css";

const poppins = Poppins({ subsets: ["latin"], weight: ["400", "500", "600", "700"], variable: "--font-poppins" });

export const metadata: Metadata = {
  title: { default: "Administration", template: "%s · Admin SOFT4DZ" },
  robots: { index: false, follow: false },
  icons: { icon: "/logo.png" },
};

export default function AdminRoot({ children }: LayoutProps<"/admin">) {
  return (
    <html lang="fr" dir="ltr" className={poppins.variable}>
      <body>{children}</body>
    </html>
  );
}
