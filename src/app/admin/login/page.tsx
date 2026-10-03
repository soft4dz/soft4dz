import Image from "next/image";
import { redirect } from "next/navigation";
import { adminConfigured, isAdmin } from "@/lib/admin-auth";
import { LoginForm } from "./LoginForm";

export const metadata = { title: "Connexion" };

export default async function LoginPage() {
  if (await isAdmin()) redirect("/admin");
  return (
    <div className="login">
      <div className="card">
        <div className="logo"><Image src="/logo.png" alt="" width={34} height={42} style={{ width: "auto" }} />SOFT4DZ</div>
        <h1>Administration</h1>
        {adminConfigured() ? <LoginForm /> : (
          <p className="note">
            L&apos;administration n&apos;est pas encore activée. Ajoutez <code>ADMIN_PASSWORD=</code> suivi d&apos;un mot de passe
            d&apos;au moins 8 caractères dans le fichier <code>site/.env.local</code>, puis redémarrez le serveur.
          </p>
        )}
      </div>
    </div>
  );
}
