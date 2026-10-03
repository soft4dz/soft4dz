import { NextResponse } from "next/server";
import { getCurrentCustomer } from "@/lib/user-auth";

export async function GET() {
  const user = await getCurrentCustomer();
  if (!user) {
    return NextResponse.json({ authenticated: false, user: null });
  }

  return NextResponse.json({
    authenticated: true,
    user: {
      id: user.id,
      name: user.name,
      email: user.email,
      avatar: user.avatar,
    },
  });
}
