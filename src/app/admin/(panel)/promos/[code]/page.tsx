import Link from "next/link";
import { notFound } from "next/navigation";
import { listPromos, type Promo } from "@/lib/promos";
import { Icon } from "@/components/Icon";
import { PromoForm } from "./PromoForm";

export const metadata = { title: "Code promo" };

export default async function PromoEdit({ params }: PageProps<"/admin/promos/[code]">) {
  const { code } = await params;
  const isNew = code === "nouveau";
  const promo: Promo | undefined = isNew
    ? { code: "", type: "percent", value: 10, minTotal: 0, maxUses: 0, uses: 0, active: true }
    : (await listPromos()).find((p) => p.code === decodeURIComponent(code));
  if (!promo) notFound();
  return (
    <>
      <div className="adm-h">
        <Link className="back" href="/admin/promos"><Icon name="arrow-left" />Codes promo</Link>
        <h1>{isNew ? "Nouveau code promo" : promo.code}</h1>
      </div>
      <PromoForm promo={promo} isNew={isNew} />
    </>
  );
}
