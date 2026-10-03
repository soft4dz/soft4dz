import { redirect, notFound } from "next/navigation";
import { hasLocale } from "@/lib/i18n";
import { getDictionary } from "@/lib/dictionaries";
import { Icon } from "@/components/Icon";

/** Suivi : le client saisit son numéro de commande (reçu par e-mail). */
export default async function TrackPage({ params }: PageProps<"/[lang]/commande">) {
  const { lang } = await params;
  if (!hasLocale(lang)) notFound();
  const d = getDictionary(lang);

  async function find(form: FormData) {
    "use server";
    const raw = String(form.get("id") ?? "").trim().toUpperCase().replace(/^#/, "");
    const id = raw.startsWith("SD-") ? raw : `SD-${raw}`;
    redirect(`/${lang}/commande/${encodeURIComponent(id)}`);
  }

  return (
    <div className="done-w" style={{ maxWidth: 520 }}>
      <div className="ok-ic wait" style={{ background: "var(--pri-50)" }}><Icon name="package-search" style={{ width: 48, height: 48, color: "var(--pri)" }} /></div>
      <h1>{d.footer.track}</h1>
      <form action={find} className="card fs" style={{ marginTop: 20, textAlign: "start" }}>
        <div className="fld">
          <input id="oid" name="id" placeholder=" " required autoComplete="off" />
          <label htmlFor="oid">{d.done.order} (SD-XXXXXXXX)</label>
        </div>
        <button className="btn b-pri" style={{ width: "100%", marginTop: 12 }}><Icon name="search" />{d.header.searchLabel}</button>
      </form>
    </div>
  );
}
