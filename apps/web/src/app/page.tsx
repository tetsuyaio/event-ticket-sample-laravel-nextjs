import Link from "next/link";

export default function Home() {
  return <div className="hero page-shell"><div className="hero-copy"><p className="eyebrow">DISCOVER YOUR NEXT MOMENT</p><h1>心が動く場所へ、<br />チケットひとつで。</h1><p>音楽、トーク、コミュニティ。あなたの次の体験を見つけて、かんたんに予約できます。</p><div className="button-row"><Link className="button primary" href="/events">イベントを探す</Link><Link className="button secondary" href="/signup">はじめる</Link></div></div><div className="hero-art" aria-hidden="true"><span>LIVE</span><strong>08</strong><small>YOUR NEXT EXPERIENCE</small></div></div>;
}
