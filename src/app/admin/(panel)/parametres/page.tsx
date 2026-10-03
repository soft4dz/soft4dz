import { getSettings } from "@/lib/settings";
import { SettingsForm } from "./SettingsForm";

export const metadata = { title: "Paramètres" };

export default async function SettingsPage() {
  const s = await getSettings();
  return (
    <>
      <div className="adm-h"><h1>Paramètres de la boutique</h1></div>
      <SettingsForm s={s} />
    </>
  );
}
