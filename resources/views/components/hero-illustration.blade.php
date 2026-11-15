{{-- Default hero illustration: laptop + smartphone showing web & mobile interfaces. Replaceable from the back office. --}}
<svg viewBox="0 0 560 400" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="hero-illustration-title" {{ $attributes }}>
    <title id="hero-illustration-title">Ordinateur portable et smartphone affichant des interfaces web et mobiles</title>
    <defs>
        <linearGradient id="hi-screen" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#FFFFFF" />
            <stop offset="1" stop-color="#EAF2FF" />
        </linearGradient>
        <linearGradient id="hi-frame" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#1E3A8A" />
            <stop offset="1" stop-color="#07133D" />
        </linearGradient>
        <linearGradient id="hi-base" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#C9D8F2" />
            <stop offset="1" stop-color="#8FA7D6" />
        </linearGradient>
        <linearGradient id="hi-image" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#BFD6FA" />
            <stop offset="1" stop-color="#9DC0F3" />
        </linearGradient>
        <filter id="hi-shadow" x="-20%" y="-20%" width="140%" height="150%">
            <feDropShadow dx="0" dy="18" stdDeviation="16" flood-color="#0755C8" flood-opacity="0.18" />
        </filter>
    </defs>

    {{-- Laptop --}}
    <g filter="url(#hi-shadow)">
        <path d="M108 52a14 14 0 0 1 14-14h318a14 14 0 0 1 14 14v236H108z" fill="url(#hi-frame)" />
        <rect x="122" y="52" width="318" height="224" rx="4" fill="url(#hi-screen)" />
        <path d="M60 296h440a6 6 0 0 1 0 0l-14 16a18 18 0 0 1-13 6H87a18 18 0 0 1-13-6z" fill="url(#hi-base)" />
        <rect x="60" y="288" width="440" height="10" rx="5" fill="#DCE6F7" />
        <rect x="248" y="288" width="64" height="5" rx="2.5" fill="#B3C5E6" />
    </g>

    {{-- Laptop screen content --}}
    <rect x="146" y="76" width="70" height="9" rx="4.5" fill="#9DC0F3" />
    <rect x="146" y="96" width="150" height="12" rx="6" fill="#BFD6FA" />
    <rect x="146" y="116" width="120" height="12" rx="6" fill="#D5E4FB" />
    <rect x="146" y="144" width="130" height="96" rx="6" fill="url(#hi-image)" />
    <path d="M152 234l34-42 22 24 16-16 46 34z" fill="#4F8FE6" opacity="0.85" />
    <circle cx="246" cy="168" r="10" fill="#EAF2FF" />
    <rect x="294" y="186" width="96" height="8" rx="4" fill="#BFD6FA" />
    <rect x="294" y="202" width="80" height="8" rx="4" fill="#D5E4FB" />
    <rect x="294" y="218" width="64" height="8" rx="4" fill="#D5E4FB" />

    {{-- Smartphone --}}
    <g filter="url(#hi-shadow)">
        <rect x="388" y="146" width="122" height="226" rx="22" fill="url(#hi-frame)" />
        <rect x="397" y="155" width="104" height="208" rx="15" fill="url(#hi-screen)" />
        <rect x="431" y="160" width="36" height="7" rx="3.5" fill="#07133D" />
    </g>
    <rect x="410" y="184" width="78" height="62" rx="6" fill="url(#hi-image)" />
    <path d="M414 242l22-26 14 15 10-9 26 20z" fill="#4F8FE6" opacity="0.85" />
    <rect x="410" y="258" width="66" height="7" rx="3.5" fill="#9DC0F3" />
    <rect x="410" y="273" width="78" height="6" rx="3" fill="#D5E4FB" />
    <rect x="410" y="286" width="58" height="6" rx="3" fill="#D5E4FB" />
    <rect x="410" y="306" width="78" height="22" rx="6" fill="#1674DB" />
    <rect x="428" y="314" width="42" height="6" rx="3" fill="#FFFFFF" opacity="0.9" />
</svg>
