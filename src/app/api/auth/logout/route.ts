import { NextResponse } from "next/server";
import { destroyCustomerSession } from "@/lib/user-auth";

export async function POST(req: Request) {
  const origin = new URL(req.url).origin;
  await destroyCustomerSession();
  return NextResponse.redirect(`${origin}/fr`);
}

export async function GET(req: Request) {
  const origin = new URL(req.url).origin;
  await destroyCustomerSession();
  return NextResponse.redirect(`${origin}/fr`);
}
