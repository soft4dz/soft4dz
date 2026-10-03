"use client";

import Link from "next/link";
import Image from "next/image";
import { usePathname } from "next/navigation";
import { Icon, type IconName } from "@/components/Icon";
import { logout } from "../actions";

const groups: { title?: string; links: { href: string; label: string; icon: IconName; exact?: boolean; badge?: "orders" | "stock" }[] }[] = [
  { links: [
    { href: "/admin", label: "Tableau de bord", icon: "layout-dashboard", exact: true },
    { href: "/admin/commandes", label: "Commandes", icon: "package", badge: "orders" },
    { href: "/admin/clients", label: "Clients", icon: "user" },
  ] },
  { title: "Catalogue", links: [
    { href: "/admin/produits", label: "Produits", icon: "store" },
    { href: "/admin/stock", label: "Stock de clés", icon: "key-round", badge: "stock" },
    { href: "/admin/bannieres", label: "Bannières", icon: "sparkles" },
    { href: "/admin/promos", label: "Codes promo", icon: "gift" },
  ] },
  { title: "Boutique", links: [
    { href: "/admin/parametres", label: "Paramètres", icon: "wrench" },
    { href: "/admin/journal", label: "Journal d'activité", icon: "receipt" },
  ] },
];

export function AdminNav({ toDeliver, lowStock }: { toDeliver: number; lowStock: number }) {
  const path = usePathname();
  return (
    <nav className="adm-nav" aria-label="Administration">
      <Link className="logo" href="/admin">
        <Image src="/logo.png" alt="" width={34} height={42} style={{ width: "auto" }} />
        <span>SOFT4DZ<small>Administration</small></span>
      </Link>
      {groups.map((g, gi) => (
        <div key={gi} className="grp">
          {g.title && <span className="gt">{g.title}</span>}
          {g.links.map((l) => {
            const on = l.exact ? path === l.href : path.startsWith(l.href);
            const n = l.badge === "orders" ? toDeliver : l.badge === "stock" ? lowStock : 0;
            return (
              <Link key={l.href} href={l.href} className={on ? "on" : ""} aria-current={on ? "page" : undefined}>
                <Icon name={l.icon} />{l.label}
                {n > 0 && <span className={`n ${l.badge === "stock" ? "warn" : ""}`} title={l.badge === "stock" ? "Stock bas" : "À traiter"}>{n}</span>}
              </Link>
            );
          })}
        </div>
      ))}
      <span className="sp" />
      <a href="/fr" target="_blank" rel="noopener"><Icon name="external-link" />Voir la boutique</a>
      <form action={logout}><button><Icon name="log-out" />Déconnexion</button></form>
    </nav>
  );
}
