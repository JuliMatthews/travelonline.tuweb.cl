<?php
// /viajes-a-medida/ — formulario de 3 pasos (rediseño 08-10-2026). Va en su
// propia carpeta (index.php) para tener URL limpia sin tocar el .htaccess.
// Envía a /api-custom-trip.php → CRM del panel + correos.
require_once __DIR__ . '/../inc/i18n.php';
require_once __DIR__ . '/../inc/client_auth.php';
require_once __DIR__ . '/../inc/google_auth.php';

$session = current_client();
$client = $session['client'] ?? null;
// Datos que vienen del cotizador rápido de la portada.
$pre = [
    'destino' => mb_substr(trim((string) ($_GET['destino'] ?? '')), 0, 120),
    'cuando' => in_array((int) ($_GET['cuando'] ?? 0), [1, 2, 3, 4], true) ? (int) $_GET['cuando'] : 1,
    'adultos' => max(1, min(20, (int) ($_GET['personas'] ?? 2))),
    'tipo' => in_array((int) ($_GET['tipo'] ?? 0), range(1, 8), true) ? (int) $_GET['tipo'] : 0,
];

$pageTitle = t('vam.title');
$pageDescription = t('vam.lead');
$activeNav = 'viajes-medida';
require __DIR__ . '/../inc/head.php';
require __DIR__ . '/../inc/header.php';

$e = fn(string $k) => htmlspecialchars(t($k));
$loc = current_locale();
// Textos que usa el JS (resumen, validación, página de gracias) en el idioma actual.
$jsKeys = ['vam.err_dest', 'vam.err_name', 'vam.err_email', 'vam.err_phone', 'vam.err_consent', 'vam.err_send', 'vam.not_set',
    'vam.sum_dest', 'vam.sum_origin', 'vam.sum_when', 'vam.sum_pax', 'vam.sum_type', 'vam.sum_inc', 'vam.sum_budget', 'vam.sum_comments',
    'vam.adult_s', 'vam.adult_p', 'vam.child_s', 'vam.child_p', 'vam.years', 'vam.child_n', 'vam.next', 'vam.send', 'vam.sending',
    'vam.th_title', 'vam.th_copy', 'vam.th_wsp_msg', 'vam.name', 'vam.email', 'vam.whatsapp', 'vam.m_ok', 'vam.signed_out',
    'vam.when_exact'];
$jsI18n = [];
foreach ($jsKeys as $k) $jsI18n[$k] = t($k);
?>
<div class="ms-wrap" id="vam"
  data-i18n="<?= htmlspecialchars(json_encode($jsI18n, JSON_UNESCAPED_UNICODE)) ?>"
  data-area-url="<?= htmlspecialchars(locale_url($loc, '/area-clientes')) ?>"
  data-locale="<?= htmlspecialchars($loc) ?>">

  <!-- ═════════ FORMULARIO ═════════ -->
  <section id="vam-form-view">
    <div class="ms-page-head">
      <div class="ms-eyebrow"><?= $e('vam.eyebrow') ?></div>
      <h1><?= $e('vam.h1') ?></h1>
      <p class="lead"><?= $e('vam.lead') ?></p>
    </div>

    <div class="ms-layout">
      <form class="ms-box ms-form" id="vam-form" novalidate>
        <div class="ms-stepper" aria-hidden="true">
          <div class="ms-st on" data-st="1"><span>1 · <?= $e('vam.step1') ?></span><div class="bar"></div></div>
          <div class="ms-st" data-st="2"><span>2 · <?= $e('vam.step2') ?></span><div class="bar"></div></div>
          <div class="ms-st" data-st="3"><span>3 · <?= $e('vam.step3') ?></span><div class="bar"></div></div>
        </div>

        <!-- Paso 1 -->
        <section data-step="1">
          <h2><?= $e('vam.s1_title') ?></h2>
          <p class="ms-sub"><?= $e('vam.s1_sub') ?></p>
          <div class="ms-grid2">
            <div class="ms-field full" id="fd-destino">
              <label for="destino"><?= $e('vam.dest') ?></label>
              <input class="ms-input" id="destino" name="destino" maxlength="160" value="<?= htmlspecialchars($pre['destino']) ?>" placeholder="<?= $e('vam.dest_ph') ?>">
              <span class="ms-err"></span>
            </div>
            <div class="ms-field">
              <label for="origen"><?= $e('vam.origin') ?></label>
              <input class="ms-input" id="origen" name="origen" maxlength="120" value="Santiago">
            </div>
            <div class="ms-field">
              <label for="cuando"><?= $e('vam.when') ?></label>
              <select class="ms-input" id="cuando" name="cuando">
                <?php foreach ([1 => 'vam.when_1', 2 => 'vam.when_2', 3 => 'vam.when_3', 5 => 'vam.when_exact', 4 => 'vam.when_4'] as $v => $k): ?>
                  <option value="<?= $v ?>"<?= $v === $pre['cuando'] ? ' selected' : '' ?>><?= $e($k) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="ms-field" id="f-ida" hidden><label for="ida"><?= $e('vam.date_from') ?></label><input class="ms-input" type="date" id="ida" name="ida"></div>
            <div class="ms-field" id="f-vuelta" hidden><label for="vuelta"><?= $e('vam.date_to') ?></label><input class="ms-input" type="date" id="vuelta" name="vuelta"></div>
            <div class="ms-field">
              <span class="ms-lbl"><?= $e('vam.adults') ?></span>
              <div class="ms-counter" data-c="adultos"><button type="button" data-d="-1" aria-label="−">−</button><output id="adultos"><?= $pre['adultos'] ?></output><button type="button" data-d="1" aria-label="+">+</button></div>
            </div>
            <div class="ms-field">
              <span class="ms-lbl"><?= $e('vam.children') ?> <span class="ms-hint"><?= $e('vam.children_hint') ?></span></span>
              <div class="ms-counter" data-c="ninos"><button type="button" data-d="-1" aria-label="−">−</button><output id="ninos">0</output><button type="button" data-d="1" aria-label="+">+</button></div>
            </div>
            <div class="ms-field full" id="f-edades" hidden>
              <span class="ms-lbl"><?= $e('vam.ages') ?></span>
              <div class="ms-ages" id="edades"></div>
              <span class="ms-hint"><?= $e('vam.ages_hint') ?></span>
            </div>
          </div>
        </section>

        <!-- Paso 2 -->
        <section data-step="2" hidden>
          <h2><?= $e('vam.s2_title') ?> <span class="ms-opt"><?= $e('vam.optional') ?></span></h2>
          <p class="ms-sub"><?= $e('vam.s2_sub') ?></p>
          <div class="ms-field full" style="margin-bottom:18px"><span class="ms-lbl"><?= $e('vam.type') ?></span>
            <div class="ms-chips2">
              <?php for ($i = 1; $i <= 8; $i++): ?>
                <label class="ms-chip"><input type="radio" name="tipo" value="<?= $i ?>"<?= $i === $pre['tipo'] ? ' checked' : '' ?>><span><?= $e("vam.type_$i") ?></span></label>
              <?php endfor; ?>
            </div>
          </div>
          <div class="ms-field full" style="margin-bottom:18px"><span class="ms-lbl"><?= $e('vam.include') ?></span>
            <div class="ms-chips2">
              <?php foreach ([1 => '✈', 2 => '🏨', 3 => '🚐', 4 => '🗺', 5 => '🛡', 6 => '🚢', 7 => '🚗'] as $i => $icon): ?>
                <label class="ms-chip"><input type="checkbox" name="incluir" value="<?= $i ?>"><span><?= $icon ?> <?= $e("vam.inc_$i") ?></span></label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="ms-field full" style="margin-bottom:18px"><span class="ms-lbl"><?= $e('vam.budget') ?> <span class="ms-hint"><?= $e('vam.budget_pp') ?></span></span>
            <div class="ms-chips2">
              <?php for ($i = 1; $i <= 6; $i++): ?>
                <label class="ms-chip"><input type="radio" name="presupuesto" value="<?= $i ?>"><span><?= $e("vam.budget_$i") ?></span></label>
              <?php endfor; ?>
            </div>
          </div>
          <div class="ms-field full"><label for="comentarios"><?= $e('vam.comments') ?></label>
            <textarea class="ms-input" id="comentarios" name="comentarios" maxlength="1200" placeholder="<?= $e('vam.comments_ph') ?>"></textarea>
          </div>
        </section>

        <!-- Paso 3 -->
        <section data-step="3" hidden>
          <h2><?= $e('vam.s3_title') ?></h2>
          <p class="ms-sub"><?= $e('vam.s3_sub') ?></p>
          <div class="ms-hint-box" id="guest-hint"<?= $client ? ' hidden' : '' ?>>
            <span><?= $e('vam.login_hint') ?></span>
            <button type="button" class="ms-btn ms-btn-s" data-open-auth style="padding:8px 14px"><?= $e('vam.login') ?></button>
          </div>
          <div class="ms-hint-box logged" id="logged-card"<?= $client ? '' : ' hidden' ?>>
            <span style="display:flex;gap:10px;align-items:center"><span class="ms-av" id="lc-av"><?= htmlspecialchars(mb_substr($client['name'] ?? 'C', 0, 1)) ?></span>
              <span><b id="lc-name"><?= htmlspecialchars($client['name'] ?? '') ?></b><span class="ms-hint" style="display:block"><?= $e('vam.logged') ?></span></span></span>
            <button type="button" class="ms-linkish" data-logout><?= $e('vam.not_you') ?></button>
          </div>
          <div class="ms-grid2">
            <div class="ms-field full" id="fd-nombre"><label for="nombre"><?= $e('vam.name') ?></label><input class="ms-input" id="nombre" name="nombre" autocomplete="name" maxlength="120" value="<?= htmlspecialchars($client['name'] ?? '') ?>"><span class="ms-err"></span></div>
            <div class="ms-field" id="fd-correo"><label for="correo"><?= $e('vam.email') ?></label><input class="ms-input" type="email" id="correo" name="correo" autocomplete="email" maxlength="160" value="<?= htmlspecialchars($client['email'] ?? '') ?>"<?= $client ? ' readonly' : '' ?>><span class="ms-err"></span></div>
            <div class="ms-field" id="fd-whatsapp"><label for="whatsapp"><?= $e('vam.whatsapp') ?></label>
              <div class="ms-phone">
                <select class="ms-input" id="codigo" name="codigo" aria-label="+56"><option value="+56">🇨🇱 +56</option><option value="+51">🇵🇪 +51</option><option value="+54">🇦🇷 +54</option><option value="+55">🇧🇷 +55</option><option value="+57">🇨🇴 +57</option><option value="+52">🇲🇽 +52</option><option value="+1">🇺🇸 +1</option><option value="+34">🇪🇸 +34</option></select>
                <input class="ms-input" type="tel" id="whatsapp" name="whatsapp" placeholder="9 1234 5678" autocomplete="tel-national" maxlength="20" value="<?= htmlspecialchars(preg_replace('/^\+?56\s*/', '', (string) ($client['phone'] ?? ''))) ?>">
              </div><span class="ms-err"></span></div>
            <input class="ms-hp" name="web" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px">
            <div class="ms-field full" id="fd-consentimiento"><label class="ms-consent"><input type="checkbox" id="consentimiento" name="consentimiento"><span><?= $e('vam.consent') ?> <a href="<?= htmlspecialchars(locale_url($loc, '/legal/politicas-de-privacidad')) ?>" target="_blank"><?= $e('vam.privacy') ?></a>.</span></label><span class="ms-err"></span></div>
          </div>
        </section>

        <p class="ms-err" id="send-error" role="alert" hidden style="margin-top:14px"></p>
        <div class="ms-actions">
          <button type="button" class="ms-btn ms-btn-s" id="back" hidden><?= $e('vam.back') ?></button>
          <span></span>
          <div style="display:flex;gap:10px">
            <button type="button" class="ms-btn ms-btn-s" id="skip" hidden><?= $e('vam.skip') ?></button>
            <button type="button" class="ms-btn ms-btn-p" id="next"><?= $e('vam.next') ?></button>
          </div>
        </div>
      </form>

      <aside class="ms-side">
        <div class="ms-box"><h3><?= $e('vam.summary') ?></h3><dl class="ms-sum" id="resumen"></dl></div>
        <div class="ms-box"><h3><?= $e('vam.next_title') ?></h3>
          <ol class="ms-flow">
            <?php for ($i = 1; $i <= 3; $i++): ?>
              <li><i><?= $i ?></i><span><b><?= $e("vam.next_{$i}_t") ?></b><?= $e("vam.next_{$i}_d") ?></span></li>
            <?php endfor; ?>
          </ol>
        </div>
      </aside>
    </div>
  </section>

  <!-- ═════════ GRACIAS ═════════ -->
  <section id="vam-thanks" class="ms-box ms-thanks" hidden aria-live="polite">
    <div class="ms-check"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></div>
    <div class="ms-eyebrow"><?= $e('vam.th_eyebrow') ?></div>
    <h1 class="ms-h2" style="margin:8px 0" id="th-title"></h1>
    <p class="ms-muted"><?= $e('vam.th_folio') ?></p>
    <div class="ms-folio" id="th-folio"></div>
    <p style="font-size:17px;font-weight:600;margin:6px 0 4px"><?= $e('vam.th_soon') ?></p>
    <p class="ms-hint" id="th-copy"></p>
    <p style="margin-top:20px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
      <a class="ms-btn ms-btn-w" id="th-wsp" href="#" target="_blank" rel="noopener noreferrer"><?= $e('vam.th_wsp') ?></a>
      <a class="ms-btn ms-btn-s" href="<?= htmlspecialchars(locale_url($loc, '/area-clientes')) ?>"><?= $e('vam.th_area') ?></a>
    </p>
    <div class="ms-detail"><div class="ms-lbl" style="margin-bottom:8px"><?= $e('vam.th_detail') ?></div><table id="th-detail"></table></div>
  </section>
</div>

<!-- Modal de acceso: no saca a la persona del formulario -->
<dialog class="ms-dialog" id="auth" aria-labelledby="auth-title">
  <button class="ms-x" type="button" data-close aria-label="✕">✕</button>
  <div class="ms-dialog-in">
    <div data-view="elegir">
      <h2 id="auth-title"><?= $e('vam.m_title') ?></h2>
      <p class="ms-sub"><?= $e('vam.m_sub') ?></p>
      <div style="display:flex;flex-direction:column;gap:10px">
        <div id="g-btn" data-client-id="<?= htmlspecialchars(GOOGLE_CLIENT_ID) ?>" style="display:flex;justify-content:center;min-height:44px"></div>
        <button class="ms-btn ms-btn-s" type="button" data-go="login"><?= $e('vam.m_email_btn') ?></button>
        <button class="ms-btn ms-btn-p" type="button" data-go="registro"><?= $e('vam.m_register') ?></button>
      </div>
      <p style="text-align:center;margin-top:14px"><button class="ms-linkish" type="button" data-close><?= $e('vam.m_guest') ?></button></p>
    </div>
    <div data-view="login" hidden>
      <h2><?= $e('vam.m_email_btn') ?></h2>
      <div class="ms-field" style="margin:14px 0 12px"><label for="a-mail"><?= $e('vam.email') ?></label><input class="ms-input" type="email" id="a-mail" autocomplete="username"></div>
      <div class="ms-field" style="margin-bottom:12px"><div style="display:flex;justify-content:space-between"><label for="a-pass"><?= $e('vam.m_pass') ?></label><a class="ms-linkish" href="<?= htmlspecialchars(locale_url($loc, '/area-clientes')) ?>" target="_blank"><?= $e('vam.m_forgot') ?></a></div><input class="ms-input" type="password" id="a-pass" autocomplete="current-password"></div>
      <p class="ms-err" id="a-err" hidden></p>
      <button class="ms-btn ms-btn-p" type="button" id="a-login" style="width:100%;margin-top:6px"><?= $e('vam.m_enter') ?></button>
      <p style="text-align:center;margin-top:14px"><button class="ms-linkish" type="button" data-go="elegir"><?= $e('vam.m_other') ?></button></p>
    </div>
    <div data-view="registro" hidden>
      <h2><?= $e('vam.m_register') ?></h2>
      <p class="ms-sub" style="margin-top:6px"><?= $e('vam.m_register_note') ?></p>
      <div style="display:flex;flex-direction:column;gap:10px">
        <a class="ms-btn ms-btn-p" href="<?= htmlspecialchars(locale_url($loc, '/area-clientes')) ?>#registro" target="_blank" rel="noopener"><?= $e('vam.m_register_go') ?></a>
        <button class="ms-btn ms-btn-s" type="button" id="a-ready"><?= $e('vam.m_ready') ?></button>
      </div>
      <p style="text-align:center;margin-top:14px"><button class="ms-linkish" type="button" data-go="elegir"><?= $e('vam.m_other') ?></button></p>
    </div>
  </div>
</dialog>
<div class="ms-toast" id="toast" role="status" hidden></div>

<script src="https://accounts.google.com/gsi/client" async defer></script>
<script src="/js/viajes-a-medida.js?v=2" defer></script>
<?php require __DIR__ . '/../inc/footer.php'; ?>
