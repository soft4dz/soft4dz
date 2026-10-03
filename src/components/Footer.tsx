"use client";

import Link from "next/link";
import Image from "next/image";
import { useI18n, useSettings } from "./providers";
import { waLink } from "@/lib/site";

export function Footer() {
  const { locale, dict } = useI18n();
  const f = dict.footer;
  const site = useSettings();
  const socials = [["Facebook", site.facebook], ["Instagram", site.instagram], ["TikTok", site.tiktok]].filter(([, u]) => u);
  return (
    <footer className="ftr">
      <div className="w fg">
        <div>
          <Link className="logo" href={`/${locale}`}>
            <Image src="/logo.png" alt="" width={40} height={50} style={{ width: "auto" }} />SOFT4DZ
          </Link>
          <p>{f.about}</p>
          {socials.length > 0 && <p>{socials.map(([n, u], i) => <span key={n}>{i > 0 && " · "}<a href={u} target="_blank" rel="noopener" style={{ display: "inline" }}>{n}</a></span>)}</p>}
        </div>
        <div>
          <h5>{f.shop}</h5>
          <Link href={`/${locale}?cat=win#catalogue`}>Windows</Link>
          <Link href={`/${locale}?cat=off#catalogue`}>Office</Link>
          <Link href={`/${locale}?cat=str#catalogue`}>{f.subscriptions}</Link>
        </div>
        <div>
          <h5>{f.help}</h5>
          <Link href={`/${locale}/commande`}>{f.track}</Link>
          <a href={waLink(site.whatsapp)} target="_blank" rel="noopener">WhatsApp</a>
          <a href={`mailto:${site.email}`}>{site.email}</a>
        </div>
        <div>
          <h5>{f.payment}</h5>
          <div className="pm"><span>CIB</span><span>Edahabia</span><span>CCP</span></div>
        </div>
      </div>
      <div className="w cp">© {new Date().getFullYear()} SOFT4DZ. {f.rights}</div>
    </footer>
  );
}
