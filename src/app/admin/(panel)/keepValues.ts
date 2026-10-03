import { startTransition } from "react";

/** Envoie un formulaire à une action sans que React le vide : en cas d'erreur, la saisie reste en place. */
export const keepValues = (action: (fd: FormData) => void) => (e: React.FormEvent<HTMLFormElement>) => {
  e.preventDefault();
  const fd = new FormData(e.currentTarget);
  startTransition(() => action(fd));
};
