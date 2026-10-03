import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  experimental: {
    serverActions: {
      // Envoi d'images depuis l'admin : 3 Mo max par fichier (+ marge pour l'enveloppe multipart)
      bodySizeLimit: "3.2mb",
    },
  },
};

export default nextConfig;
