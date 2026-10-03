import Link from "next/link";
import { listAudit } from "@/lib/audit";

export const metadata = { title: "Journal d'activité" };

export default async function AuditPage() {
  const entries = await listAudit();
  return (
    <>
      <div className="adm-h"><h1>Journal d&apos;activité</h1><small className="muted-s">500 dernières actions</small></div>
      <div className="card tbl-w">
        <table className="tbl">
          <thead><tr><th>Date</th><th>Action</th><th>Élément</th></tr></thead>
          <tbody>
            {entries.length === 0 && <tr><td colSpan={3} className="empty-row">Aucune action enregistrée.</td></tr>}
            {entries.map((e, i) => (
              <tr key={i}>
                <td className="muted" style={{ whiteSpace: "nowrap" }}>{new Date(e.at).toLocaleString("fr-FR", { dateStyle: "short", timeStyle: "medium" })}</td>
                <td>{e.action.startsWith("Échec") ? <span className="stt s-failed">{e.action}</span> : e.action}</td>
                <td className="muted">{e.target?.startsWith("SD-") ? <Link className="row-link" href={`/admin/commandes/${e.target}`}>{e.target}</Link> : e.target}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </>
  );
}
