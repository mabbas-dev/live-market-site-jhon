<?php
/**
 * LIVETVMARKET - Admin Multi-Currency & GeoIP Manager
 * Configure exchange rates ($ = GBP, EUR, PKR, etc.), symbols, assigned countries,
 * and automatic IP-based localization.
 */
$adminTitle = 'Currency & GeoIP Setup';
$adminPage = 'currencies';

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/currency.php';

$successMsg = '';
$errorMsg = '';

// Handle Currency Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        $symbol = trim($_POST['symbol'] ?? '$');
        $rate = (float)($_POST['rate'] ?? 1.0);
        $assignedCountries = strtoupper(trim($_POST['assigned_countries'] ?? ''));
        $isDefault = isset($_POST['is_default']) ? 1 : 0;
        $isEnabled = isset($_POST['is_enabled']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        // Sanitize comma-separated country codes
        $cleanedCountries = [];
        foreach (explode(',', $assignedCountries) as $cnt) {
            $cnt = strtoupper(trim($cnt));
            if (!empty($cnt)) {
                $cleanedCountries[] = $cnt;
            }
        }
        $assignedCountries = implode(',', array_unique($cleanedCountries));

        if (empty($code) || empty($name) || empty($symbol) || $rate <= 0) {
            $errorMsg = "Please fill in all required fields. Exchange rate must be greater than 0.";
        } else {
            try {
                // If setting as default, clear default on other currencies
                if ($isDefault) {
                    $pdo->exec("UPDATE `currencies` SET `is_default` = 0");
                }

                if ($id > 0) {
                    // Update
                    $stmt = $pdo->prepare("UPDATE `currencies` SET 
                        `code` = ?, `name` = ?, `symbol` = ?, `rate` = ?, 
                        `assigned_countries` = ?, `is_default` = ?, `is_enabled` = ?, `sort_order` = ?
                        WHERE `id` = ?");
                    $stmt->execute([$code, $name, $symbol, $rate, $assignedCountries, $isDefault, $isEnabled, $sortOrder, $id]);
                    $successMsg = "Currency '{$code}' updated successfully!";
                } else {
                    // Check duplicate code
                    $chk = $pdo->prepare("SELECT COUNT(*) FROM `currencies` WHERE `code` = ?");
                    $chk->execute([$code]);
                    if ($chk->fetchColumn() > 0) {
                        $errorMsg = "Currency code '{$code}' already exists.";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO `currencies` 
                            (`code`, `name`, `symbol`, `rate`, `assigned_countries`, `is_default`, `is_enabled`, `sort_order`) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$code, $name, $symbol, $rate, $assignedCountries, $isDefault, $isEnabled, $sortOrder]);
                        $successMsg = "New currency '{$code}' added successfully!";
                    }
                }
            } catch (Exception $e) {
                $errorMsg = "Database error: " . $e->getMessage();
            }
        }
    } else if ($action === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $curr = $pdo->prepare("SELECT `is_default`, `is_enabled` FROM `currencies` WHERE `id` = ?");
            $curr->execute([$id]);
            $row = $curr->fetch();
            if ($row && $row['is_default']) {
                $errorMsg = "Cannot disable the default base currency.";
            } else {
                $pdo->prepare("UPDATE `currencies` SET `is_enabled` = IF(`is_enabled` = 1, 0, 1) WHERE `id` = ?")->execute([$id]);
                $successMsg = "Currency status updated!";
            }
        } catch (Exception $e) {
            $errorMsg = "Error toggling status: " . $e->getMessage();
        }
    } else if ($action === 'set_default') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $pdo->beginTransaction();
            $pdo->exec("UPDATE `currencies` SET `is_default` = 0");
            $pdo->prepare("UPDATE `currencies` SET `is_default` = 1, `is_enabled` = 1 WHERE `id` = ?")->execute([$id]);
            $pdo->commit();
            $successMsg = "Default base currency updated successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $errorMsg = "Error setting default currency: " . $e->getMessage();
        }
    } else if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $curr = $pdo->prepare("SELECT `is_default`, `code` FROM `currencies` WHERE `id` = ?");
            $curr->execute([$id]);
            $row = $curr->fetch();
            if ($row && $row['is_default']) {
                $errorMsg = "Cannot delete the default base currency ({$row['code']}).";
            } else {
                $pdo->prepare("DELETE FROM `currencies` WHERE `id` = ?")->execute([$id]);
                $successMsg = "Currency deleted successfully!";
            }
        } catch (Exception $e) {
            $errorMsg = "Error deleting currency: " . $e->getMessage();
        }
    }
}

// Fetch all currencies
$allCurrencies = getCurrencies(false);
$defaultCurr = getDefaultCurrency();
$enabledCurrenciesCount = count(array_filter($allCurrencies, fn($c) => !empty($c['is_enabled'])));
?>

<div class="max-w-6xl">

  <!-- TOP HEADER -->
  <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-black text-white flex items-center gap-2.5">
        <i data-lucide="coins" class="w-6 h-6 text-amber-400"></i>
        <span>Multi-Currency & GeoIP Setup</span>
      </h1>
      <p class="text-xs text-slate-400 mt-1">
        Configure exchange rates ($1 USD = X GBP, EUR, PKR, etc.), customize symbols, and map target countries for automatic IP-based currency switching.
      </p>
    </div>

    <div class="flex items-center gap-3">
      <button type="button" onclick="openCurrencyModal()" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-bold text-xs flex items-center gap-2 transition-all shadow-md shadow-amber-500/20 cursor-pointer">
        <i data-lucide="plus-circle" class="w-4 h-4"></i>
        <span>Add New Currency</span>
      </button>
    </div>
  </div>

  <!-- SUCCESS / ERROR ALERTS -->
  <?php if (!empty($successMsg)): ?>
    <div class="mb-6 p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 text-xs flex items-center gap-3">
      <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400 flex-shrink-0"></i>
      <span><?= htmlspecialchars($successMsg) ?></span>
    </div>
  <?php endif; ?>

  <?php if (!empty($errorMsg)): ?>
    <div class="mb-6 p-4 rounded-2xl bg-rose-500/15 border border-rose-500/40 text-rose-300 text-xs flex items-center gap-3">
      <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-400 flex-shrink-0"></i>
      <span><?= htmlspecialchars($errorMsg) ?></span>
    </div>
  <?php endif; ?>

  <!-- METRIC SUMMARY CARDS -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    
    <div class="glass-card p-5 rounded-2xl relative overflow-hidden">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-[11px] font-mono uppercase tracking-wider text-slate-400 font-bold">Base Currency</p>
          <h3 class="text-2xl font-black text-white mt-1"><?= htmlspecialchars($defaultCurr['code']) ?> (<?= htmlspecialchars($defaultCurr['symbol']) ?>)</h3>
        </div>
        <div class="w-10 h-10 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center font-bold text-sm">
          $1.00
        </div>
      </div>
      <p class="text-[10px] text-cyan-300/80 font-mono mt-2">Anchor rate for all conversions</p>
    </div>

    <div class="glass-card p-5 rounded-2xl relative overflow-hidden">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-[11px] font-mono uppercase tracking-wider text-slate-400 font-bold">Enabled Currencies</p>
          <h3 class="text-2xl font-black text-emerald-400 mt-1"><?= $enabledCurrenciesCount ?> of <?= count($allCurrencies) ?></h3>
        </div>
        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
          <i data-lucide="check-circle-2" class="w-5 h-5"></i>
        </div>
      </div>
      <p class="text-[10px] text-slate-400 mt-2">Active on storefront & checkout</p>
    </div>

    <div class="glass-card p-5 rounded-2xl relative overflow-hidden">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-[11px] font-mono uppercase tracking-wider text-slate-400 font-bold">Key Target Markets</p>
          <div class="flex items-center gap-1.5 mt-2 flex-wrap">
            <span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-300 font-mono text-[10px] font-bold">USA ($)</span>
            <span class="px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 font-mono text-[10px] font-bold">EUR (€)</span>
            <span class="px-2 py-0.5 rounded bg-purple-500/20 text-purple-300 font-mono text-[10px] font-bold">GBP (£)</span>
            <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-mono text-[10px] font-bold">PKR (Rs)</span>
          </div>
        </div>
      </div>
    </div>

    <div class="glass-card p-5 rounded-2xl relative overflow-hidden">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-[11px] font-mono uppercase tracking-wider text-slate-400 font-bold">GeoIP Detection</p>
          <h3 class="text-sm font-bold text-white mt-1 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Silent IP-Based</span>
          </h3>
        </div>
        <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center">
          <i data-lucide="globe-2" class="w-5 h-5"></i>
        </div>
      </div>
      <p class="text-[10px] text-slate-400 mt-2">Zero browser permission prompts</p>
    </div>

  </div>

  <!-- CURRENCIES DATA TABLE -->
  <div class="glass-card rounded-3xl overflow-hidden border border-slate-800 mb-10 shadow-xl">
    
    <div class="p-6 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-black text-white flex items-center gap-2">
          <i data-lucide="table-2" class="w-5 h-5 text-cyan-400"></i>
          <span>Currencies & Exchange Rates Configuration</span>
        </h2>
        <p class="text-xs text-slate-400 mt-0.5">
          Order plans ($14.99 1-month and $74.99 12-month) are converted automatically based on these rates.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <span class="text-xs text-slate-400 font-mono"><?= count($allCurrencies) ?> Currencies Configured</span>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-900/80 text-slate-400 uppercase font-mono text-[10px] border-b border-slate-800">
          <tr>
            <th class="p-4">Currency</th>
            <th class="p-4">Symbol</th>
            <th class="p-4">Exchange Rate ($1 USD =)</th>
            <th class="p-4">Sample 1-Mo ($14.99)</th>
            <th class="p-4">Sample 12-Mo ($74.99)</th>
            <th class="p-4">Assigned Countries (IP Mapped)</th>
            <th class="p-4">Status</th>
            <th class="p-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60 text-slate-300">
          <?php foreach ($allCurrencies as $c): 
            $rate = (float)$c['rate'];
            $sym = $c['symbol'];
            $sample1m = ($c['code'] === 'PKR') ? ($sym . ' ' . number_format(14.99 * $rate, 0)) : ($sym . number_format(14.99 * $rate, 2));
            $sample12m = ($c['code'] === 'PKR') ? ($sym . ' ' . number_format(74.99 * $rate, 0)) : ($sym . number_format(74.99 * $rate, 2));
            $assignedList = array_filter(array_map('trim', explode(',', $c['assigned_countries'] ?? '')));
          ?>
            <tr class="hover:bg-slate-800/30 transition-colors">
              <td class="p-4">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center font-mono font-black text-xs text-white">
                    <?= htmlspecialchars($c['code']) ?>
                  </div>
                  <div>
                    <span class="font-bold text-white text-xs block"><?= htmlspecialchars($c['name']) ?></span>
                    <?php if (!empty($c['is_default'])): ?>
                      <span class="px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 font-mono text-[9px] font-bold border border-cyan-500/40">
                        ★ Base Default
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
              </td>

              <td class="p-4">
                <span class="font-mono font-black text-base text-cyan-400 bg-slate-900 px-2.5 py-1 rounded-lg border border-slate-700">
                  <?= htmlspecialchars($c['symbol']) ?>
                </span>
              </td>

              <td class="p-4">
                <div class="font-mono font-bold text-white text-sm">
                  1 USD = <span class="text-amber-400"><?= rtrim(rtrim(number_format($c['rate'], 4), '0'), '.') ?></span> <?= htmlspecialchars($c['code']) ?>
                </div>
              </td>

              <td class="p-4 font-mono font-bold text-slate-300">
                <?= $sample1m ?>
              </td>

              <td class="p-4 font-mono font-bold text-emerald-400">
                <?= $sample12m ?>
              </td>

              <td class="p-4 max-w-xs">
                <?php if (!empty($assignedList)): ?>
                  <div class="flex flex-wrap gap-1">
                    <?php foreach (array_slice($assignedList, 0, 8) as $cnt): ?>
                      <span class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 font-mono text-[10px] border border-slate-700">
                        <?= htmlspecialchars($cnt) ?>
                      </span>
                    <?php endforeach; ?>
                    <?php if (count($assignedList) > 8): ?>
                      <span class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 font-mono text-[10px]">
                        +<?= count($assignedList) - 8 ?> more
                      </span>
                    <?php endif; ?>
                  </div>
                <?php else: ?>
                  <span class="text-slate-500 text-[11px] italic">None (Fallback to default)</span>
                <?php endif; ?>
              </td>

              <td class="p-4">
                <?php if (!empty($c['is_enabled'])): ?>
                  <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    Active
                  </span>
                <?php else: ?>
                  <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-800 text-slate-500 border border-slate-700">
                    Disabled
                  </span>
                <?php endif; ?>
              </td>

              <td class="p-4 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  
                  <!-- Edit Button -->
                  <button type="button" onclick="editCurrency(<?= htmlspecialchars(json_encode($c)) ?>)" title="Edit Currency" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-cyan-400 transition-colors cursor-pointer">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                  </button>

                  <!-- Toggle Enable/Disable -->
                  <?php if (empty($c['is_default'])): ?>
                    <form method="POST" class="inline" onsubmit="return confirm('Toggle active status for <?= htmlspecialchars($c['code']) ?>?');">
                      <input type="hidden" name="action" value="toggle_status">
                      <input type="hidden" name="id" value="<?= $c['id'] ?>">
                      <button type="submit" title="<?= !empty($c['is_enabled']) ? 'Disable Currency' : 'Enable Currency' ?>" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-400 transition-colors cursor-pointer">
                        <i data-lucide="<?= !empty($c['is_enabled']) ? 'eye-off' : 'eye' ?>" class="w-4 h-4"></i>
                      </button>
                    </form>
                  <?php endif; ?>

                  <!-- Set Default Button -->
                  <?php if (empty($c['is_default'])): ?>
                    <form method="POST" class="inline" onsubmit="return confirm('Set <?= htmlspecialchars($c['code']) ?> as base default currency?');">
                      <input type="hidden" name="action" value="set_default">
                      <input type="hidden" name="id" value="<?= $c['id'] ?>">
                      <button type="submit" title="Make Base Default" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-emerald-400 transition-colors cursor-pointer">
                        <i data-lucide="star" class="w-4 h-4"></i>
                      </button>
                    </form>
                  <?php endif; ?>

                  <!-- Delete Button -->
                  <?php if (empty($c['is_default'])): ?>
                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete currency <?= htmlspecialchars($c['code']) ?>?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $c['id'] ?>">
                      <button type="submit" title="Delete Currency" class="p-2 rounded-lg bg-slate-800 hover:bg-rose-500/20 text-rose-400 transition-colors cursor-pointer">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                      </button>
                    </form>
                  <?php endif; ?>

                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </div>

  <!-- GEOIP SIMULATOR & TESTER CARD -->
  <div class="glass-card p-6 sm:p-8 rounded-3xl border border-slate-800 mb-12">
    <div class="flex items-center gap-3 mb-4">
      <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold">
        <i data-lucide="radar" class="w-5 h-5"></i>
      </div>
      <div>
        <h3 class="text-base font-bold text-white">Live GeoIP Country & Currency Simulation</h3>
        <p class="text-xs text-slate-400">Test how the automated IP detection resolves different countries without needing a VPN.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Select Test Country Code</label>
        <select id="geoip-test-select" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400">
          <option value="US">🇺🇸 United States (US)</option>
          <option value="GB">🇬🇧 United Kingdom (GB)</option>
          <option value="DE">🇩🇪 Germany (DE - Eurozone)</option>
          <option value="FR">🇫🇷 France (FR - Eurozone)</option>
          <option value="PK">🇵🇰 Pakistan (PK)</option>
          <option value="CA">🇨🇦 Canada (CA)</option>
          <option value="AU">🇦🇺 Australia (AU)</option>
          <option value="AE">🇦🇪 United Arab Emirates (AE)</option>
          <option value="IN">🇮🇳 India (IN)</option>
        </select>
      </div>

      <div class="md:col-span-2 flex items-center gap-3">
        <button type="button" onclick="runGeoTest()" class="btn-glow-cyan px-5 py-2.5 rounded-xl text-xs font-bold whitespace-nowrap cursor-pointer">
          Simulate Detection
        </button>
        <div id="geoip-test-result" class="p-2.5 rounded-xl bg-slate-900 border border-slate-700 text-xs font-mono text-cyan-300 flex-1 text-center">
          Click Simulate
        </div>
      </div>
    </div>
  </div>

</div>

<!-- ADD / EDIT CURRENCY MODAL -->
<div id="currency-modal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md hidden flex items-center justify-center p-4">
  <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-lg w-full p-6 sm:p-8 relative shadow-2xl">
    
    <button type="button" onclick="closeCurrencyModal()" class="absolute top-5 right-5 p-2 rounded-xl bg-slate-800 text-slate-400 hover:text-white transition-colors cursor-pointer">
      <i data-lucide="x" class="w-5 h-5"></i>
    </button>

    <div class="flex items-center gap-3 mb-6">
      <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center">
        <i data-lucide="dollar-sign" class="w-5 h-5"></i>
      </div>
      <div>
        <h3 class="font-heading font-black text-xl text-white" id="modal-heading">Add New Currency</h3>
        <p class="text-xs text-slate-400">Configure exchange rate and assigned ISO country codes.</p>
      </div>
    </div>

    <form method="POST" id="currency-form" class="space-y-4">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" id="curr-id" value="0">

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Currency Code *</label>
          <input type="text" name="code" id="curr-code" required placeholder="e.g. PKR, GBP, EUR" uppercase maxlength="8"
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white font-mono font-bold text-xs focus:border-amber-400 focus:outline-none uppercase">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Currency Symbol *</label>
          <input type="text" name="symbol" id="curr-symbol" required placeholder="e.g. Rs, $, £, €" maxlength="8"
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white font-mono font-bold text-xs focus:border-amber-400 focus:outline-none">
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-300 mb-1">Full Currency Name *</label>
        <input type="text" name="name" id="curr-name" required placeholder="e.g. Pakistani Rupee, British Pound"
          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs focus:border-amber-400 focus:outline-none">
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-300 mb-1">
          Exchange Rate vs $1.00 USD *
          <span class="text-slate-500 font-normal">($1 USD = how many of this currency?)</span>
        </label>
        <input type="number" step="0.0001" min="0.0001" name="rate" id="curr-rate" required placeholder="e.g. 278.50"
          oninput="updateModalRatePreview()"
          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-amber-300 font-mono font-bold text-sm focus:border-amber-400 focus:outline-none">
      </div>

      <!-- Live Calculation Preview -->
      <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 text-xs font-mono space-y-1">
        <div class="text-slate-400 flex justify-between">
          <span>1 USD =</span>
          <span id="rate-preview-1" class="text-amber-400 font-bold">$1.00</span>
        </div>
        <div class="text-slate-400 flex justify-between">
          <span>1-Month Plan ($14.99 USD) =</span>
          <span id="rate-preview-14" class="text-white font-bold">$14.99</span>
        </div>
        <div class="text-slate-400 flex justify-between">
          <span>12-Month Plan ($74.99 USD) =</span>
          <span id="rate-preview-74" class="text-emerald-400 font-bold">$74.99</span>
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-300 mb-1">
          Assigned Country Codes (ISO 2-letter, comma separated)
        </label>
        <input type="text" name="assigned_countries" id="curr-countries" placeholder="e.g. PK or GB,UK or US,UM"
          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white font-mono text-xs focus:border-amber-400 focus:outline-none uppercase">
        <p class="text-[10px] text-slate-500 mt-1">Visitors matching these countries by IP will automatically see this currency.</p>
        
        <!-- Quick Tag Shortcuts -->
        <div class="flex flex-wrap gap-1.5 mt-2">
          <button type="button" onclick="appendCountry('PK')" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-[10px] font-mono text-slate-300">+ PK</button>
          <button type="button" onclick="appendCountry('GB,UK')" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-[10px] font-mono text-slate-300">+ GB, UK</button>
          <button type="button" onclick="appendCountry('DE,FR,IT,ES,NL,BE,AT,IE,PT,GR,FI')" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-[10px] font-mono text-slate-300">+ Eurozone</button>
          <button type="button" onclick="appendCountry('US,UM')" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-[10px] font-mono text-slate-300">+ US</button>
          <button type="button" onclick="appendCountry('CA')" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-[10px] font-mono text-slate-300">+ CA</button>
          <button type="button" onclick="appendCountry('AU')" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-[10px] font-mono text-slate-300">+ AU</button>
          <button type="button" onclick="appendCountry('AE,SA,QA,KW,OM,BH')" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-[10px] font-mono text-slate-300">+ Gulf / GCC</button>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4 pt-2">
        <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-300">
          <input type="checkbox" name="is_enabled" id="curr-enabled" checked value="1" class="w-4 h-4 rounded accent-cyan-500">
          <span>Enabled on Store</span>
        </label>

        <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-300">
          <input type="checkbox" name="is_default" id="curr-default" value="1" class="w-4 h-4 rounded accent-amber-500">
          <span>Make Base Default</span>
        </label>
      </div>

      <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
        <button type="button" onclick="closeCurrencyModal()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold cursor-pointer">
          Cancel
        </button>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold transition-all shadow-md shadow-amber-500/20 cursor-pointer">
          Save Currency Setup
        </button>
      </div>
    </form>

  </div>
</div>

<script>
const currencyModal = document.getElementById('currency-modal');

function openCurrencyModal() {
  document.getElementById('modal-heading').textContent = 'Add New Currency';
  document.getElementById('curr-id').value = '0';
  document.getElementById('currency-form').reset();
  document.getElementById('curr-enabled').checked = true;
  document.getElementById('curr-default').checked = false;
  updateModalRatePreview();
  currencyModal.classList.remove('hidden');
  if (window.lucide) window.lucide.createIcons();
}

function closeCurrencyModal() {
  currencyModal.classList.add('hidden');
}

function editCurrency(c) {
  document.getElementById('modal-heading').textContent = 'Edit ' + c.code + ' Currency';
  document.getElementById('curr-id').value = c.id;
  document.getElementById('curr-code').value = c.code;
  document.getElementById('curr-name').value = c.name;
  document.getElementById('curr-symbol').value = c.symbol;
  document.getElementById('curr-rate').value = c.rate;
  document.getElementById('curr-countries').value = c.assigned_countries || '';
  document.getElementById('curr-enabled').checked = (parseInt(c.is_enabled) === 1);
  document.getElementById('curr-default').checked = (parseInt(c.is_default) === 1);
  updateModalRatePreview();
  currencyModal.classList.remove('hidden');
  if (window.lucide) window.lucide.createIcons();
}

function appendCountry(code) {
  const input = document.getElementById('curr-countries');
  let current = input.value.trim();
  if (!current) {
    input.value = code;
  } else {
    const list = current.split(',').map(s => s.trim().toUpperCase());
    const toAdd = code.split(',').map(s => s.trim().toUpperCase());
    toAdd.forEach(c => {
      if (!list.includes(c)) list.push(c);
    });
    input.value = list.join(',');
  }
}

function updateModalRatePreview() {
  const symbol = document.getElementById('curr-symbol')?.value.trim() || '$';
  const code = document.getElementById('curr-code')?.value.trim() || 'USD';
  const rate = parseFloat(document.getElementById('curr-rate')?.value) || 1.0;

  const isPkr = (code.toUpperCase() === 'PKR');
  const fmt = (v) => isPkr ? (symbol + ' ' + Math.round(v).toLocaleString()) : (symbol + v.toFixed(2));

  document.getElementById('rate-preview-1').textContent = fmt(1.0 * rate) + ' ' + code;
  document.getElementById('rate-preview-14').textContent = fmt(14.99 * rate);
  document.getElementById('rate-preview-74').textContent = fmt(74.99 * rate);
}

// GeoIP Simulator
function runGeoTest() {
  const code = document.getElementById('geoip-test-select').value;
  const currencies = <?= json_encode($allCurrencies) ?>;
  
  let matchedCurr = null;
  for (const c of currencies) {
    if (parseInt(c.is_enabled) !== 1) continue;
    const assigned = (c.assigned_countries || '').split(',').map(s => s.trim().toUpperCase());
    if (assigned.includes(code.toUpperCase())) {
      matchedCurr = c;
      break;
    }
  }

  if (!matchedCurr) {
    matchedCurr = currencies.find(c => parseInt(c.is_default) === 1) || currencies[0];
  }

  const rate = parseFloat(matchedCurr.rate);
  const sym = matchedCurr.symbol;
  const isPkr = (matchedCurr.code === 'PKR');
  const val = isPkr ? (sym + ' ' + Math.round(74.99 * rate).toLocaleString()) : (sym + (74.99 * rate).toFixed(2));

  document.getElementById('geoip-test-result').innerHTML = `
    Detected: <strong class="text-white">${code}</strong> → Currency: <strong class="text-emerald-400">${matchedCurr.code} (${matchedCurr.symbol})</strong> | 12-Mo Plan: <span class="text-cyan-300 font-bold">${val}</span>
  `;
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

