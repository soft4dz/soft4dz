"use client";

import { useRouter } from "next/navigation";
import { useEffect, useRef, useState } from "react";
import { Icon, type IconName } from "@/components/Icon";
import { useCart, useCatalog, useI18n } from "@/components/providers";
import { Stepper } from "@/components/checkout/Stepper";
import { usePromoCheck } from "@/components/checkout/usePromoCheck";
import { finalPrice } from "@/lib/catalog";
import { formatNumber, currency } from "@/lib/format";
import { paymentMethods, rules, type CheckoutField } from "@/lib/validation";
import { t } from "@/lib/i18n";

const logos: Record<(typeof paymentMethods)[number], [string, string]> = {
  cib: ["CIB", "#1D4577"],
  edahabia: ["EDAHABIA", "#D9A21B"],
  ccp: ["CCP", "#626B78"],
};
const infoIcon: Record<(typeof paymentMethods)[number], IconName> = { cib: "lock", edahabia: "lock", ccp: "clock" };

export default function CheckoutPage() {
  const { locale, dict } = useI18n();
  const cart = useCart();
  const { get: getProduct } = useCatalog();
  const router = useRouter();
  const k = dict.checkout;
  const cur = currency(locale);
  const [values, setValues] = useState({ name: "", phone: "", email: "" });
  const [errors, setErrors] = useState<Partial<Record<CheckoutField | "terms", boolean>>>({});
  const [method, setMethod] = useState<(typeof paymentMethods)[number]>("cib");
  const [terms, setTerms] = useState(false);
  const [busy, setBusy] = useState(false);
  const promo = usePromoCheck(cart.promo, cart.lines);
  const discount = promo?.ok ? promo.discount : 0;
  const total = cart.subtotal - discount;
  const [failed, setFailed] = useState(false);
  const refs = { name: useRef<HTMLInputElement>(null), phone: useRef<HTMLInputElement>(null), email: useRef<HTMLInputElement>(null) };

  // Panier vide : retour au panier
  useEffect(() => {
    if (cart.ready && !cart.lines.length && !busy) router.replace(`/${locale}/panier`);
  }, [cart.ready, cart.lines.length, busy, locale, router]);

  const set = (f: CheckoutField) => (e: React.ChangeEvent<HTMLInputElement>) => {
    setValues({ ...values, [f]: e.target.value });
    if (errors[f]) setErrors({ ...errors, [f]: false });
  };

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    const errs: typeof errors = {};
    (Object.keys(rules) as CheckoutField[]).forEach((f) => { if (!rules[f](values[f])) errs[f] = true; });
    if (!terms) errs.terms = true;
    setErrors(errs);
    const first = (["name", "phone", "email"] as const).find((f) => errs[f]);
    if (first) { refs[first].current?.focus(); return; }
    if (errs.terms) return;

    setBusy(true);
    setFailed(false);
    try {
      const res = await fetch("/api/checkout", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ locale, customer: values, method, lines: cart.lines, promo: promo?.ok ? promo.code : undefined }),
      });
      const data = await res.json();
      if (!res.ok || !data.redirect) throw new Error(data.error);
      cart.clear();
      window.location.assign(data.redirect);
    } catch {
      setFailed(true);
      setBusy(false);
    }
  };

  const field = (f: CheckoutField, label: string, err: string, type = "text", mode?: "tel" | "email") => (
    <div className={`fld ${errors[f] ? "err" : ""}`}>
      <input ref={refs[f]} id={`f-${f}`} type={type} inputMode={mode} placeholder=" " value={values[f]} onChange={set(f)}
        aria-invalid={!!errors[f]} aria-describedby={errors[f] ? `e-${f}` : undefined} autoComplete={f === "name" ? "name" : f === "phone" ? "tel" : "email"} />
      <label htmlFor={`f-${f}`}>{label}</label>
      {errors[f] && <div className="em" id={`e-${f}`}>{err}</div>}
    </div>
  );

  return (
    <>
      <Stepper step={2} />
      <div className="co">
        <form onSubmit={submit} noValidate id="pay">
          <div className="card fs">
            <h3><i>1</i>{k.contact}</h3>
            <div className="grid2">
              {field("name", k.name, k.nameErr)}
              {field("phone", k.phone, k.phoneErr, "tel", "tel")}
            </div>
            <div style={{ marginTop: 12 }}>{field("email", k.email, k.emailErr, "email", "email")}</div>
            <div className="hint"><Icon name="mail" />{k.emailHint}</div>
          </div>

          <div className="card fs">
            <h3><i>2</i>{k.method}</h3>
            <div className="pmg" role="radiogroup">
              {paymentMethods.map((m) => (
                <label key={m}>
                  <input type="radio" name="pm" value={m} checked={method === m} onChange={() => setMethod(m)} />
                  <span className="lg" style={{ background: logos[m][1] }}>{logos[m][0]}</span>
                  <span><b>{k.methods[m][0]}</b><small>{k.methods[m][1]}</small></span>
                </label>
              ))}
            </div>
            <div className="pinfo" key={method}><Icon name={infoIcon[method]} /><span>{k.info[method]}</span></div>
            <label className="chk">
              <input type="checkbox" checked={terms} onChange={(e) => { setTerms(e.target.checked); setErrors({ ...errors, terms: false }); }} />
              {k.terms}
            </label>
            {errors.terms && <div className="ferr">{k.termsErr}</div>}
          </div>
        </form>

        <aside className="card sum">
          <h3>{k.order}</h3>
          {cart.lines.map((l) => {
            const p = getProduct(l.slug)!;
            return (
              <div className="row" key={`${l.slug}-${l.option}`}>
                <span>{t(p.name, locale)} <small style={{ color: "var(--muted)" }}>× {l.qty}</small></span>
                <b>{formatNumber(finalPrice(p, l.option) * l.qty)} {cur}</b>
              </div>
            );
          })}
          {discount > 0 && <div className="row disc"><span>{dict.cart.discount} ({promo?.ok && promo.code})</span><b>-{formatNumber(discount)} {cur}</b></div>}
          <div className="row tot"><span>{dict.cart.total}</span><b>{formatNumber(total)} {cur}</b></div>
          <button className="btn b-or" form="pay" disabled={busy}>
            {busy ? <><Icon name="loader" className="spin" />{k.paying}</> : <><Icon name="lock" />{k.pay} {formatNumber(total)} {cur}</>}
          </button>
          {failed && <div className="ferr" role="alert">{k.error}</div>}
          <div className="sec"><Icon name="shield-check" />{k.secure}</div>
        </aside>
      </div>
    </>
  );
}
