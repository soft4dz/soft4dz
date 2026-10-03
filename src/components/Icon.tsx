import {
  ArrowRight, BadgeCheck, Bot, Briefcase, Check, CheckCircle2, ChevronLeft, ChevronRight, Clapperboard,
  Clock, Cloud, CreditCard, FileSpreadsheet, FileText, Flame, Gift, Globe, Headset, Heart, KeyRound,
  LayoutDashboard, LayoutGrid, Loader2, Lock, Mail, MessageCircle, Minus, Monitor, Music, PackageSearch,
  Palette, PenTool, Play, Plus, Popcorn, RefreshCcw, Search, Server, Shield, ShieldCheck, ShoppingBag,
  ShoppingCart, Sparkles, Star, Store, Trash2, Tv, User, Wrench, Zap, Info, Receipt, Landmark,
  Package, LogOut, ExternalLink, Pencil, Eye, EyeOff, TrendingUp, Wallet, Copy, Save, ArrowLeft, Filter, ArrowUp, ArrowDown, X, SendHorizontal, MessagesSquare,
  type LucideProps,
} from "lucide-react";

/* Seules les icônes utilisées sont importées : le bundle reste léger. */
const map = {
  "arrow-right": ArrowRight, "badge-check": BadgeCheck, bot: Bot, briefcase: Briefcase, check: Check,
  "check-circle-2": CheckCircle2, "chevron-left": ChevronLeft, "chevron-right": ChevronRight,
  clapperboard: Clapperboard, clock: Clock, cloud: Cloud, "credit-card": CreditCard,
  "file-spreadsheet": FileSpreadsheet, "file-text": FileText, flame: Flame, gift: Gift, globe: Globe,
  headset: Headset, heart: Heart, "key-round": KeyRound, "layout-dashboard": LayoutDashboard,
  "layout-grid": LayoutGrid, loader: Loader2, lock: Lock, mail: Mail, "message-circle": MessageCircle,
  minus: Minus, monitor: Monitor, music: Music, "package-search": PackageSearch, palette: Palette,
  "pen-tool": PenTool, play: Play, plus: Plus, popcorn: Popcorn, "refresh-ccw": RefreshCcw, search: Search,
  server: Server, shield: Shield, "shield-check": ShieldCheck, "shopping-bag": ShoppingBag,
  "shopping-cart": ShoppingCart, sparkles: Sparkles, star: Star, store: Store, trash: Trash2, tv: Tv,
  user: User, wrench: Wrench, zap: Zap, info: Info, receipt: Receipt, landmark: Landmark,
  package: Package, "log-out": LogOut, "external-link": ExternalLink, pencil: Pencil, eye: Eye, "eye-off": EyeOff,
  "trending-up": TrendingUp, wallet: Wallet, copy: Copy, save: Save, "arrow-left": ArrowLeft, filter: Filter, "arrow-up": ArrowUp, "arrow-down": ArrowDown, x: X, send: SendHorizontal, chat: MessagesSquare,
};

export type IconName = keyof typeof map;
export const iconNames = Object.keys(map) as IconName[];

export function Icon({ name, ...props }: { name: IconName } & LucideProps) {
  const C = map[name];
  return <C aria-hidden="true" {...props} />;
}
