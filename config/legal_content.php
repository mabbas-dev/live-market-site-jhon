<?php
/**
 * LIVETVMARKET - Default Legal & Policy Pages Content
 * Comprehensive, production-ready legal agreements for streaming subscription service.
 */

return [
    'terms' => [
        'slug'             => 'terms',
        'title'            => 'Terms of Service & Subscriber Agreement',
        'badge'            => 'Legal Agreement',
        'icon'             => 'file-text',
        'last_updated'     => 'November 2026',
        'meta_description' => 'Official Terms of Service and Subscriber Agreement for LIVETVMARKET. Outlines acceptable use, subscription terms, CDN infrastructure SLA, and account policies.',
        'content'          => <<<HTML
<div class="space-y-8">
  <div class="p-6 rounded-2xl bg-cyan-50/70 border border-cyan-200 text-slate-800 text-sm leading-relaxed">
    <strong class="text-cyan-900 font-bold block mb-1">Subscriber Agreement Overview</strong>
    Please read these Terms of Service carefully before purchasing a subscription or initiating a 24-hour test line with <strong>LIVETVMARKET</strong>. By accessing our website, purchasing any subscription plan, or utilizing our streaming credentials, you acknowledge that you have read, understood, and agreed to be legally bound by this agreement.
  </div>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">01</span>
      Acceptance of Terms & Eligibility
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      By creating an order or requesting trial access through LIVETVMARKET, you affirm that you are at least 18 years of age (or have reached the age of majority in your jurisdiction) and possess the legal capacity to enter into binding contracts. If you are accepting these terms on behalf of an entity or household, you warrant that you are authorized to bind all end users of the issued credentials.
    </p>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">02</span>
      Service Delivery & Anti-Freeze™ 9.9 CDN Infrastructure
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      LIVETVMARKET provides access to private streaming syndication clusters engineered with our proprietary Anti-Freeze™ 9.9 edge caching architecture. Following successful payment confirmation, streaming credentials (including M3U Plus playlists and Xtream Codes API access) are provisioned automatically and delivered to your designated email address and WhatsApp number within 60 seconds.
    </p>
    <ul class="list-disc pl-5 text-sm text-slate-600 space-y-1">
      <li><strong>Broadcast Quality:</strong> Feeds are transmitted in 4K UHD, 1080p FHD 60FPS, and 720p HD based on available source broadcasts and your connection speed.</li>
      <li><strong>Internet Bandwidth Requirements:</strong> A minimum stable broadband connection of 25 Mbps is recommended for HD streaming, and 50+ Mbps for uninterrupted 4K 60FPS live sports broadcasts.</li>
      <li><strong>ISP Throttling & VPN Compatibility:</strong> Our clusters are fully compatible with external VPN providers. If your ISP employs deep packet inspection (DPI) to throttle video streams, you are permitted to use a VPN to maintain maximum speed.</li>
    </ul>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">03</span>
      Permitted Connections & Device Policy
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      Each standard subscription plan is licensed for <strong>one (1) concurrent connection</strong> unless additional simultaneous connections were explicitly purchased during checkout.
    </p>
    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs leading-relaxed">
      <strong>Important Concurrent Streaming Rule:</strong> You may configure your credentials on multiple personal devices (such as an Amazon Firestick, Smart TV, and mobile phone), but you may only stream actively on <em>one device at a time</em> per purchased connection. Attempting concurrent streaming across multiple screens simultaneously on a 1-connection line will trigger automated server freeze protections to prevent credential sharing.
    </div>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">04</span>
      Prohibited Uses & Anti-Abuse Policy
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      Subscribers must use their access exclusively for personal, private, non-commercial entertainment. The following actions are strictly prohibited and will result in immediate credential revocation without refund:
    </p>
    <ul class="list-disc pl-5 text-sm text-slate-600 space-y-1.5">
      <li>Re-streaming, rebroadcasting, syndicating, or publicly performing feeds in commercial venues, bars, or public establishments.</li>
      <li>Scraping, capturing, downloading, or reverse engineering server stream tokens or proprietary playlist structures.</li>
      <li>Publishing, sharing, or reselling issued M3U URLs or Xtream passwords on public forums, Discord servers, or social media.</li>
      <li>Launching DDoS attacks, unauthorized port scans, or abusive high-frequency API polling against our cluster balancers.</li>
    </ul>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">05</span>
      Billing, Pricing & Subscription Terms
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      All subscription prices are quoted in USD (or your localized currency equivalent). Payments are processed through certified, PCI-DSS compliant payment gateways with 256-bit SSL encryption. We do not maintain recurring stealth billing; subscriptions operate on fixed-term access (1, 3, 12, or 24 months). You will receive an expiration notice prior to plan expiry with the option to renew.
    </p>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">06</span>
      Service Availability SLA & Maintenance
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      While LIVETVMARKET targets 99.99% server cluster uptime, occasional emergency maintenance, satellite uplink adjustments, or CDN failovers may occur. Temporary channel guide (EPG) recalibrations or playlist refreshing do not constitute a material breach of this agreement.
    </p>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">07</span>
      Limitation of Liability
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      To the maximum extent permitted by applicable law, LIVETVMARKET, its operators, officers, and technical partners shall not be liable for any indirect, incidental, punitive, or consequential damages arising from the use of or inability to use the service, including ISP outages, local hardware incompatibilities, or third-party app software updates.
    </p>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">08</span>
      Modifications & Contact
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      We reserve the right to update these Terms of Service periodically to reflect infrastructure enhancements or regulatory adjustments. Continued use of the platform after updates constitute acceptance. For inquiries regarding this agreement, reach our legal support team via our 24/7 VIP WhatsApp desk or email at <strong>support@livetvmarket.com</strong>.
    </p>
  </section>
</div>
HTML
    ],

    'privacy' => [
        'slug'             => 'privacy',
        'title'            => 'Privacy & Data Protection Policy',
        'badge'            => 'Zero-Logging SLA',
        'icon'             => 'shield-check',
        'last_updated'     => 'November 2026',
        'meta_description' => 'LIVETVMARKET official Privacy Policy. We maintain a strict Zero-Logging policy. Learn how we safeguard your email, order details, and browsing anonymity.',
        'content'          => <<<HTML
<div class="space-y-8">
  <div class="p-6 rounded-2xl bg-emerald-50/70 border border-emerald-200 text-slate-800 text-sm leading-relaxed">
    <strong class="text-emerald-900 font-bold block mb-1">Our Core Privacy Commitment: Zero Activity Logging</strong>
    At <strong>LIVETVMARKET</strong>, privacy is not an afterthought—it is foundational to our architecture. We operate an uncompromising <strong>Zero-Logging streaming infrastructure</strong>. We do not monitor, record, inspect, or store your viewing history, channels watched, search queries, or video streams.
  </div>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">01</span>
      Information We Collect
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      We only collect the absolute minimum information required to generate your streaming line, process transactions, and deliver automated setup instructions:
    </p>
    <ul class="list-disc pl-5 text-sm text-slate-600 space-y-1.5">
      <li><strong>Contact Information:</strong> Customer Full Name, Email Address (for playlist and credential delivery), and WhatsApp Number (for priority setup dispatch and account support).</li>
      <li><strong>Device & Application Preference:</strong> Target device (e.g. Amazon Firestick 4K, Smart TV, Android Box) and preferred app (e.g. IPTV Smarters, TiviMate) selected during checkout to tailor optimal playlist format.</li>
      <li><strong>Transaction Data:</strong> Subscription tier, order timestamp, amount, and gateway transaction identifiers. (We <em>never</em> receive, handle, or store credit card numbers).</li>
    </ul>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">02</span>
      Information We NEVER Collect or Store
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      To protect your complete anonymity, our edge CDN clusters are configured without persistent user telemetry:
    </p>
    <ul class="list-disc pl-5 text-sm text-slate-600 space-y-1.5">
      <li>We do NOT track or store viewing logs, channel switching habits, or video titles accessed.</li>
      <li>We do NOT perform deep packet inspection on streaming traffic.</li>
      <li>We do NOT maintain persistent connection history or IP address mapping.</li>
      <li>We do NOT install trackers, third-party advertising SDKs, or behavioral analytics cookies.</li>
    </ul>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">03</span>
      Payment Security & PCI-DSS Compliance
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      Credit Card and debit transactions are processed through certified, bank-grade payment processing gateways utilizing SHA-256 HMAC cryptographic signatures and TLS 1.3 encryption. Your financial payment credentials remain strictly between your browser and the acquiring processor; our web servers never see or log sensitive credit card numbers or CVV codes.
    </p>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">04</span>
      Cookies & GeoIP Localization
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      Our storefront uses minimal, transient session cookies solely to:
    </p>
    <ul class="list-disc pl-5 text-sm text-slate-600 space-y-1">
      <li>Remember your currency selection (e.g. USD, EUR, GBP, CAD) across pages.</li>
      <li>Perform silent IP geolocation to present relevant local pricing without intrusive permission prompts.</li>
      <li>Preserve shopping cart state and active promotional coupons during checkout.</li>
    </ul>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">05</span>
      Third-Party Disclosures & No Sale of Data
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      LIVETVMARKET has never sold, rented, leased, or monetized customer data, and will never do so. We only share operational order data with essential infrastructure partners (such as transactional email dispatch servers and payment gateway processors) strictly as required to fulfill your streaming line order.
    </p>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">06</span>
      Your Rights & Data Deletion
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      Under applicable privacy laws (including GDPR and CCPA), you hold the right to request full extraction or permanent erasure of your order record and contact details from our database. To request immediate data removal, submit a ticket via our Contact page or message our 24/7 VIP WhatsApp support desk.
    </p>
  </section>
</div>
HTML
    ],

    'refund-policy' => [
        'slug'             => 'refund-policy',
        'title'            => 'Refund, Guarantee & Cancellation Policy',
        'badge'            => '7-Day Money Back SLA',
        'icon'             => 'refresh-cw',
        'last_updated'     => 'November 2026',
        'meta_description' => 'LIVETVMARKET official Refund Policy. Learn about our 7-Day Money-Back Guarantee, refund eligibility criteria, cancellation rules, and hassle-free claim procedure.',
        'content'          => <<<HTML
<div class="space-y-8">
  <div class="p-6 rounded-2xl bg-cyan-50/70 border border-cyan-200 text-slate-800 text-sm leading-relaxed">
    <strong class="text-cyan-900 font-bold block mb-1">Our 100% Risk-Free Guarantee</strong>
    At <strong>LIVETVMARKET</strong>, we stand behind the stability and performance of our Anti-Freeze™ 9.9 CDN streaming infrastructure. We offer an unconditional <strong>7-Day Money-Back Guarantee</strong> on all new subscription plans so you can test our 25,000+ live channels, 120,000+ VOD catalog, and 4K live sports with total confidence.
  </div>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">01</span>
      7-Day Money-Back Guarantee Window
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      If you experience technical issues that our 24/7 technical engineering team is unable to resolve within 48 hours of purchase, or if the service does not perform to your expectations on your designated device, you may request a <strong>100% full refund</strong> within seven (7) days of your original payment date.
    </p>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">02</span>
      Eligible Refund Scenarios
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      Refunds are promptly approved under any of the following circumstances:
    </p>
    <ul class="list-disc pl-5 text-sm text-slate-600 space-y-1.5">
      <li><strong>Non-Delivery of Credentials:</strong> Failure of the system to dispatch your M3U playlist link or Xtream Codes login within 2 hours of payment confirmation.</li>
      <li><strong>Unresolved Channel Outage:</strong> Material server downtime or feed unavailability affecting major broadcast clusters for more than 24 continuous hours.</li>
      <li><strong>Hardware Incompatibility:</strong> Device incompatibility verified by our technical staff despite following standard setup guides for Firestick, Android, or Smart TV.</li>
      <li><strong>Accidental Duplicate Payment:</strong> Inadvertent double charging for the same subscription order.</li>
    </ul>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">03</span>
      Non-Refundable Circumstances
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      To ensure fairness and prevent service abuse, refunds cannot be issued under the following conditions:
    </p>
    <ul class="list-disc pl-5 text-sm text-slate-600 space-y-1.5">
      <li>Requests submitted after the expiration of the 7-day guarantee window.</li>
      <li>Subscribers whose accounts have been terminated due to violations of our Terms of Service (such as illegal rebroadcasting, re-streaming, or credential sharing).</li>
      <li>Local internet issues caused by the subscriber's ISP (e.g. broadband line disconnection, local Wi-Fi interference, or low bandwidth under 15 Mbps). Our support team will assist with VPN configurations, but subscriber ISP outages are beyond our control.</li>
      <li>Failure to contact support before requesting a refund when an issue could be resolved by refreshing playlist DNS or updating player app settings.</li>
    </ul>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">04</span>
      Test Line Recommendation
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      We strongly encourage prospective subscribers to utilize our <strong>24-Hour Test Line</strong> prior to purchasing 12-month or 24-month multi-year packages. Testing the service on your exact television and home broadband connection ensures total peace of mind before making a long-term commitment.
    </p>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">05</span>
      Refund Processing Timeline & Method
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      Once approved by our billing desk, refunds are processed back to the original payment method (Credit/Debit Card) within <strong>24 to 48 business hours</strong>. Depending on your financial institution, the credit will appear on your banking statement within 3 to 7 business days.
    </p>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">06</span>
      How to Request a Refund
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      To submit a refund request, provide your <strong>Order Reference #LTV-XXXX</strong> and email address via:
    </p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
      <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
        <strong class="text-slate-900 block text-xs font-bold mb-1">VIP WhatsApp Desk (Fastest)</strong>
        <p class="text-xs text-slate-600">Message our live billing agent directly for priority same-day review.</p>
      </div>
      <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
        <strong class="text-slate-900 block text-xs font-bold mb-1">Email Support Ticket</strong>
        <p class="text-xs text-slate-600">Send an email to <strong>support@livetvmarket.com</strong> with your order ID.</p>
      </div>
    </div>
  </section>
</div>
HTML
    ],

    'dmca' => [
        'slug'             => 'dmca',
        'title'            => 'DMCA & Digital Content Policy',
        'badge'            => 'Intellectual Property',
        'icon'             => 'scale',
        'last_updated'     => 'November 2026',
        'meta_description' => 'LIVETVMARKET DMCA & Digital Content Policy. Outlines compliance with 17 U.S.C. 512, designated copyright agent details, and takedown notice submission procedures.',
        'content'          => <<<HTML
<div class="space-y-8">
  <div class="p-6 rounded-2xl bg-cyan-50/70 border border-cyan-200 text-slate-800 text-sm leading-relaxed">
    <strong class="text-cyan-900 font-bold block mb-1">DMCA Compliance Notice</strong>
    <strong>LIVETVMARKET</strong> respects the intellectual property rights of copyright holders and operates in strict accordance with the Digital Millennium Copyright Act (17 U.S.C. § 512) and international copyright directives. It is our policy to respond expeditiously to clear, valid notices of alleged copyright infringement.
  </div>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">01</span>
      Platform Architecture & Service Provider Status
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      LIVETVMARKET functions as an authenticated streaming intermediary gateway providing routing and proxy syndication infrastructure. We do not host, store, or broadcast media files on our web servers. All video streams and broadcast channels are transmitted from third-party media distribution networks and uplink carriers. We do not exercise editorial control over third-party broadcasts.
    </p>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">02</span>
      Notice & Takedown Procedure
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      If you are a copyright owner, or an agent authorized to act on behalf of an owner, and believe that content accessible through our platform infringes upon your copyright, you may submit a formal notification to our Designated DMCA Agent containing the following elements:
    </p>
    <ul class="list-disc pl-5 text-sm text-slate-600 space-y-1.5">
      <li>A physical or electronic signature of a person authorized to act on behalf of the owner of an exclusive right that is allegedly infringed.</li>
      <li>Identification of the copyrighted work claimed to have been infringed, or, if multiple works are covered by a single notification, a representative list of such works.</li>
      <li>Specific identification of the material that is claimed to be infringing or to be the subject of infringing activity, including stream URL, channel name, or specific identifier sufficient to permit us to locate the material.</li>
      <li>Information reasonably sufficient to permit us to contact you, such as your full legal name, company name, address, telephone number, and email address.</li>
      <li>A statement that you have a good faith belief that use of the material in the manner complained of is not authorized by the copyright owner, its agent, or the law.</li>
      <li>A statement that the information in the notification is accurate, and under penalty of perjury, that you are authorized to act on behalf of the owner of an exclusive right that is allegedly infringed.</li>
    </ul>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">03</span>
      Designated DMCA Agent Contact Information
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      All formal DMCA notifications should be directed to our Legal & Intellectual Property Compliance Department:
    </p>
    <div class="p-5 rounded-2xl bg-slate-900 text-slate-200 text-xs font-mono space-y-2 border border-slate-800">
      <div><strong>LIVETVMARKET Legal & Copyright Department</strong></div>
      <div>Attn: Designated Copyright Agent</div>
      <div>Website: <span class="text-cyan-400">https://livetvmarket.com</span></div>
      <div>Compliance Email: <span class="text-cyan-400">dmca@livetvmarket.com</span> / <span class="text-cyan-400">support@livetvmarket.com</span></div>
      <div>Subject Line Required: <span class="text-amber-400 font-bold">[DMCA TAKEDOWN REQUEST] - Content Identifier</span></div>
    </div>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">04</span>
      Counter-Notification Procedure
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      If you believe that your content was removed or access was disabled as a result of mistake or misidentification, you may submit a formal counter-notification containing your physical/electronic signature, identification of the material removed, a statement under penalty of perjury of good faith belief, and consent to jurisdiction.
    </p>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">05</span>
      Repeat Infringer Policy
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      In accordance with 17 U.S.C. § 512(i)(1)(A), LIVETVMARKET maintains an explicit policy for the immediate termination of subscriber accounts that are determined to be repeat infringers or engaged in unauthorized commercial redistributions.
    </p>
  </section>
</div>
HTML
    ],

    'copyright' => [
        'slug'             => 'copyright',
        'title'            => 'Copyright & Intellectual Property Notice',
        'badge'            => 'Brand Attribution',
        'icon'             => 'copyright',
        'last_updated'     => 'November 2026',
        'meta_description' => 'LIVETVMARKET Copyright and Trademark Notice. Outlines proprietary platform assets, third-party trademark attributions, and fair use guidelines.',
        'content'          => <<<HTML
<div class="space-y-8">
  <div class="p-6 rounded-2xl bg-cyan-50/70 border border-cyan-200 text-slate-800 text-sm leading-relaxed">
    <strong class="text-cyan-900 font-bold block mb-1">Copyright & Trademark Ownership Statement</strong>
    This Copyright and Intellectual Property Notice governs the ownership, protection, and fair use of all materials, visual assets, software interfaces, and third-party brand attributions displayed across <strong>LIVETVMARKET</strong>.
  </div>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">01</span>
      Proprietary Website Assets
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      All original elements comprising the LIVETVMARKET website—including but not limited to the user interface design, visual layouts, website code, Anti-Freeze™ 9.9 trademarks, logos, custom iconography, and written documentation—are the exclusive intellectual property of LIVETVMARKET. All rights reserved.
    </p>
    <ul class="list-disc pl-5 text-sm text-slate-600 space-y-1">
      <li>Unauthorized copying, scraping, reproducing, or republishing of our website assets is strictly prohibited without prior written consent.</li>
      <li>Automated bots, scrapers, and data extraction scripts are blocked by web application firewall policies.</li>
    </ul>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">02</span>
      Third-Party Trademarks & Channel Logos
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      All third-party brand names, channel trademarks, network logos, sports league emblems (such as NFL, NBA, Premier League, UEFA, Formula 1), and movie title banners referenced across our channel catalog and guide listings belong solely to their respective registered trademark owners.
    </p>
    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs leading-relaxed">
      <strong>Nominative Fair Use Disclaimer:</strong> The display of channel trademarks and network badges is strictly intended for nominative identification purposes to inform prospective subscribers of broadcast compatibility and channel availability. LIVETVMARKET claims no affiliation, sponsorship, endorsement, or commercial association with any television network, studio, or sports league.
    </div>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">03</span>
      Prohibition on Commercial Exploitation
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      Subscribers are strictly prohibited from copying, capturing, modifying, distributing, or commercializing any portion of the streamed content, user interface components, or API data feeds provided through their account.
    </p>
  </section>

  <section class="space-y-3">
    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">04</span>
      Reporting Intellectual Property Concerns
    </h3>
    <p class="text-sm text-slate-600 leading-relaxed">
      If you represent a brand or intellectual property holder and have questions regarding trademark attribution, logo usage, or licensing compliance, please reach our compliance team directly at <strong>legal@livetvmarket.com</strong> or <strong>support@livetvmarket.com</strong>. All communications are reviewed within 24 business hours.
    </p>
  </section>
</div>
HTML
    ]
];
