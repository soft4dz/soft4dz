<?php $pageTitle = 'Services'; ?>
<div class="page-shell">
  <!-- Hero -->
  <div class="page-hero">
    <div style="position:absolute;inset:0;background:radial-gradient(ellipse 60% 80% at 50% 100%,rgba(124,58,237,0.2) 0%,transparent 70%);pointer-events:none"></div>
    <div class="container page-hero-inner">
      <div class="eyebrow" style="display:inline-block;margin-bottom:1rem">Développement Sur Mesure</div>
      <h1 class="page-title">Nous construisons votre <span class="gradient-text">vision digitale</span></h1>
      <p class="page-subtitle">Applications web, mobile, SaaS, API — notre équipe d'experts crée des solutions digitales performantes et scalables.</p>
    </div>
  </div>

  <div class="container page-section">
    <!-- Services Grid -->
    <div class="section-header" style="margin-bottom:3rem">
      <div class="eyebrow">Ce que nous faisons</div>
      <h2>Nos domaines <span class="gradient-text">d'expertise</span></h2>
    </div>

    <div class="features-grid" style="margin-bottom:5rem">
      <?php
      $services = [
        ['icon'=>'bi-globe2',       'title'=>'Applications Web',       'desc'=>'Sites web modernes, plateformes e-commerce, portails, et applications full-stack avec les dernières technologies.', 'tech'=>['PHP','React','Laravel','Vue.js']],
        ['icon'=>'bi-phone',        'title'=>'Applications Mobile',    'desc'=>'Apps natives iOS/Android ou cross-platform avec une expérience utilisateur optimale.', 'tech'=>['Flutter','React Native','Swift','Kotlin']],
        ['icon'=>'bi-cloud-arrow-up','title'=>'Développement SaaS',   'desc'=>'Plateforme SaaS multi-tenant scalable avec facturation, dashboard, et API robuste.', 'tech'=>['AWS','Stripe','JWT','MySQL']],
        ['icon'=>'bi-shop',         'title'=>'E-commerce',             'desc'=>'Boutiques en ligne complètes avec gestion de stocks, paiements locaux et livraison.', 'tech'=>['WooCommerce','Custom','API']],
        ['icon'=>'bi-code-slash',   'title'=>'APIs & Intégrations',    'desc'=>'APIs RESTful/GraphQL, intégrations tierces (paiement, livraison, CRM, ERP).', 'tech'=>['REST','GraphQL','Webhook']],
        ['icon'=>'bi-lightbulb',    'title'=>'Consulting & Audit',     'desc'=>'Audit de code, architecture système, conseil en transformation digitale.', 'tech'=>['Architecture','Sécurité','Performance']],
      ];
      foreach ($services as $s):
      ?>
      <div class="feature-card">
        <div class="feature-icon"><i class="bi <?= $s['icon'] ?>"></i></div>
        <h4 class="feature-title"><?= $s['title'] ?></h4>
        <p class="feature-desc"><?= $s['desc'] ?></p>
        <div style="display:flex;flex-wrap:wrap;gap:0.375rem;margin-top:1rem">
          <?php foreach ($s['tech'] as $t): ?>
          <span style="font-size:0.68rem;background:rgba(124,58,237,0.1);color:var(--primary-light);border:1px solid rgba(124,58,237,0.15);border-radius:4px;padding:0.2rem 0.5rem;font-weight:600"><?= $t ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Request Form -->
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-xl);padding:3rem;max-width:800px;margin:0 auto">
      <div style="text-align:center;margin-bottom:2.5rem">
        <div class="eyebrow" style="display:inline-block;margin-bottom:1rem">Démarrez votre projet</div>
        <h2>Décrivez votre projet</h2>
        <p style="color:var(--text-secondary);margin-top:0.5rem">Remplissez ce formulaire et recevez un devis sous 24h.</p>
      </div>

      <?php $success = flash('success'); if ($success): ?>
      <div class="alert alert-success mb-6"><i class="bi bi-check-circle-fill"></i> <?= e($success) ?></div>
      <?php endif; ?>

      <form action="<?= url('services/request') ?>" method="POST">
        <?= csrf() ?>

        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Nom complet *</label>
            <input type="text" name="name" class="form-control" placeholder="Votre nom" required value="<?= \App\Core\Auth::user()['name'] ?? '' ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control" placeholder="vous@exemple.com" required value="<?= \App\Core\Auth::user()['email'] ?? '' ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Téléphone</label>
            <input type="tel" name="phone" class="form-control" placeholder="+213 XX XX XX XX">
          </div>
          <div class="form-group">
            <label class="form-label">Entreprise</label>
            <input type="text" name="company" class="form-control" placeholder="Nom de votre société">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Type de service *</label>
          <select name="service_type" class="form-control" required>
            <option value="">-- Sélectionner --</option>
            <option value="web_app">Application Web</option>
            <option value="mobile_app">Application Mobile</option>
            <option value="saas">Plateforme SaaS</option>
            <option value="ecommerce">E-commerce</option>
            <option value="api">API & Intégration</option>
            <option value="consulting">Consulting & Audit</option>
            <option value="other">Autre</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Titre du projet *</label>
          <input type="text" name="title" class="form-control" placeholder="Ex: Plateforme de gestion RH" required>
        </div>

        <div class="form-group">
          <label class="form-label">Description détaillée *</label>
          <textarea name="description" class="form-control" rows="6" required
                    placeholder="Décrivez votre projet en détail : fonctionnalités attendues, public cible, contraintes techniques..."></textarea>
        </div>

        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Budget estimé</label>
            <select name="budget_range" class="form-control">
              <option value="">-- Budget --</option>
              <option value="< 50K DZD">Moins de 50 000 DZD</option>
              <option value="50K-200K DZD">50 000 - 200 000 DZD</option>
              <option value="200K-500K DZD">200 000 - 500 000 DZD</option>
              <option value="> 500K DZD">Plus de 500 000 DZD</option>
              <option value="À discuter">À discuter</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Date limite souhaitée</label>
            <input type="date" name="deadline" class="form-control" min="<?= date('Y-m-d') ?>">
          </div>
        </div>

        <button type="submit" class="btn btn-gradient btn-xl w-full">
          <i class="bi bi-send"></i> Envoyer ma demande
        </button>
        <p style="text-align:center;font-size:0.78rem;color:var(--text-muted);margin-top:1rem">
          <i class="bi bi-clock"></i> Réponse garantie sous 24h ouvrables
        </p>
      </form>
    </div>
  </div>
</div>
