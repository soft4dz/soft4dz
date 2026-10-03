import { requireAdmin } from "@/lib/admin-auth";
import { listOrders } from "@/lib/orders";
import { lowStockCount } from "@/lib/admin-stats";
import { AdminNav } from "./AdminNav";

export default async function PanelLayout({ children }: LayoutProps<"/admin">) {
  await requireAdmin();
  const [orders, lowStock] = await Promise.all([listOrders(), lowStockCount()]);
  const toDeliver = orders.filter((o) => o.status === "paid" || o.status === "awaiting_transfer").length;
  return (
    <div className="adm">
      <AdminNav toDeliver={toDeliver} lowStock={lowStock} />
      <main className="adm-main">{children}</main>
    </div>
  );
}
