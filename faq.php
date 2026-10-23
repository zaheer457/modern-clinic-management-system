<?php
$title = 'FAQs';
require __DIR__ . '/includes/header.php';

$faqs = db_all("SELECT * FROM faqs WHERE status=1 ORDER BY id");
?>

<style>
/* =====================================================
   FAQ PAGE — COMPACT PREMIUM HEALTHCARE DESIGN
===================================================== */

/* FAQ page background */
body > main.faq-page {
    margin: 0 !important;
    padding: 0 0 60px !important;
    position: relative !important;
    top: 0 !important;
    transform: none !important;
    overflow: hidden;
    background:
        radial-gradient(ellipse at 8% 15%, rgba(69,213,223,.09), transparent 35%),
        radial-gradient(ellipse at 92% 38%, rgba(21,151,212,.07), transparent 32%);
}

.faq-page {
    --faq-navy: #08243a;
    --faq-blue: #1597d4;
    --faq-cyan: #45d5df;
    --faq-muted: #6b7d8d;
}

/* Remove top gap from FAQ hero */
body > main.faq-page > .faq-hero {
    position: relative;
    top: 0 !important;
    margin-top: 0 !important;
    padding: 24px 0 30px !important;
    min-height: auto !important;
    overflow: hidden;
    border-radius: 0 0 25px 25px;
    background: linear-gradient(
        125deg,
        #08243a 0%,
        #103e5a 56%,
        #126c89 100%
    );
    color: #fff;
}

/* Decorative hero details */
.faq-hero::before,
.faq-hero::after {
    content: "";
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
}

.faq-hero::before {
    width: 300px;
    height: 300px;
    top: -180px;
    right: 8%;
    border: 1px solid rgba(255,255,255,.12);
    box-shadow:
        0 0 0 35px rgba(255,255,255,.025),
        0 0 0 70px rgba(255,255,255,.02);
}

.faq-hero::after {
    width: 210px;
    height: 210px;
    bottom: -155px;
    left: 8%;
    background: rgba(69,213,223,.12);
    filter: blur(5px);
}

.faq-hero-content {
    position: relative;
    z-index: 1;
    max-width: 760px;
    margin: 0 auto;
    text-align: center;
}

.faq-kicker {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 7px 13px;
    border: 1px solid rgba(137,239,245,.27);
    border-radius: 50px;
    background: rgba(255,255,255,.075);
    color: #a8f4f5;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.4px;
    text-transform: uppercase;
}

.faq-kicker-dot {
    width: 7px;
    height: 7px;
    flex: 0 0 7px;
    border-radius: 50%;
    background: #45d5df;
    box-shadow: 0 0 12px rgba(69,213,223,.8);
}

.faq-hero h1 {
    margin: 14px 0 9px !important;
    color: #fff;
    font-size: clamp(28px, 4vw, 40px) !important;
    line-height: 1.2;
    font-weight: 800;
    letter-spacing: -1px;
}

.faq-hero h1 span {
    color: #70e4e8;
}

.faq-hero-description {
    max-width: 570px;
    margin: 0 auto;
    color: rgba(238,249,255,.82);
    font-size: 14px;
    line-height: 1.6;
}

/* Search bar */
.faq-search-wrap {
    position: relative;
    max-width: 590px;
    margin: 18px auto 0 !important;
}

.faq-search-icon {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 2;
    color: #1597d4;
    font-size: 19px;
    pointer-events: none;
}

.faq-search-input {
    display: block;
    width: 100%;
    min-height: 51px;
    padding: 13px 48px 13px 52px;
    border: 1px solid rgba(255,255,255,.35);
    border-radius: 15px;
    outline: none;
    background: #fff;
    color: #163047;
    font-size: 14px;
    box-shadow: 0 10px 28px rgba(0,0,0,.14);
    transition: border-color .2s, box-shadow .2s;
}

.faq-search-input::placeholder {
    color: #8193a2;
}

.faq-search-input:focus {
    border-color: #70e4e8;
    box-shadow:
        0 0 0 4px rgba(69,213,223,.18),
        0 10px 28px rgba(0,0,0,.14);
}

.faq-search-clear {
    position: absolute;
    top: 50%;
    right: 14px;
    transform: translateY(-50%);
    display: none;
    width: 30px;
    height: 30px;
    border: 0;
    border-radius: 9px;
    background: #eaf7fb;
    color: #31576d;
    font-size: 18px;
    cursor: pointer;
}

.faq-search-clear.visible {
    display: grid;
    place-items: center;
}

/* FAQ content */
.faq-content {
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
    padding-top: 27px !important;
}

.faq-section-heading {
    margin-bottom: 23px;
    text-align: center;
}

.faq-section-heading .small-label {
    display: block;
    margin-bottom: 8px;
    color: #1597d4;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.8px;
    text-transform: uppercase;
}

.faq-section-heading h2 {
    margin: 0 0 8px;
    color: #08243a;
    font-size: clamp(24px, 3vw, 32px);
    font-weight: 800;
    letter-spacing: -.7px;
}

.faq-section-heading p {
    margin: 0;
    color: #6b7d8d;
    font-size: 14px;
    line-height: 1.7;
}

/* FAQ accordion */
.faq-list {
    display: grid;
    gap: 13px;
}

.faq-page .faq-item {
    overflow: hidden;
    margin-bottom: 13px;
    border: 1px solid #e1edf3 !important;
    border-radius: 16px !important;
    background: linear-gradient(120deg, #fff 0%, #f8fcfe 100%);
    box-shadow: 0 5px 18px rgba(8,36,58,.045) !important;
    transition: border-color .25s, box-shadow .25s, transform .25s;
}

.faq-page .faq-item:hover {
    border-color: #b9e8ef !important;
    box-shadow: 0 9px 25px rgba(21,151,212,.09) !important;
    transform: translateY(-1px);
}

.faq-page .accordion-button {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 18px 22px;
    background: transparent;
    color: #163047;
    font-size: 15px;
    line-height: 1.6;
    font-weight: 700;
    box-shadow: none !important;
}

.faq-page .accordion-button:not(.collapsed) {
    color: #087eae;
    background: linear-gradient(100deg, #effbfc 0%, #f5fbff 100%);
}

.faq-number {
    flex: 0 0 35px;
    display: grid;
    place-items: center;
    width: 35px;
    height: 35px;
    border: 1px solid #d6edf4;
    border-radius: 11px;
    background: linear-gradient(145deg, #eaf9fc, #edf5ff);
    color: #118bbd;
    font-size: 12px;
    font-weight: 800;
}

.faq-page .accordion-button::after {
    flex: 0 0 auto;
    width: 13px;
    height: 13px;
    background-size: 13px;
    opacity: .7;
}

.faq-page .accordion-button:not(.collapsed)::after {
    opacity: 1;
}

.faq-page .accordion-body {
    padding: 0 26px 21px 72px;
    color: #63798a;
    font-size: 14px;
    line-height: 1.9;
    white-space: normal;
}

.faq-answer-text {
    padding-top: 4px;
    overflow-wrap: anywhere;
}

/* Search results */
.faq-results-status {
    margin: 0 0 15px;
    color: #6b7d8d;
    font-size: 13px;
    text-align: center;
}

.faq-empty-state {
    display: none;
    padding: 36px 22px;
    border: 1px dashed #b9dce8;
    border-radius: 20px;
    background: linear-gradient(135deg, #f3fbfd, #f8fbff);
    text-align: center;
}

.faq-empty-state.visible {
    display: block;
}

.faq-empty-icon {
    display: grid;
    place-items: center;
    width: 58px;
    height: 58px;
    margin: 0 auto 15px;
    border-radius: 18px;
    background: linear-gradient(135deg, #dff8fa, #e7f2ff);
    color: #128bb7;
    font-size: 25px;
}

.faq-empty-state h3 {
    margin-bottom: 8px;
    color: #163047;
    font-size: 18px;
    font-weight: 800;
}

.faq-empty-state p {
    margin: 0;
    color: #6b7d8d;
    font-size: 14px;
}

/* Contact panel */
.faq-help-panel {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    margin-top: 30px;
    padding: 25px 28px;
    border: 1px solid rgba(69,213,223,.25);
    border-radius: 20px;
    background:
        radial-gradient(circle at 95% 10%, rgba(69,213,223,.13), transparent 33%),
        linear-gradient(115deg, #eefafd 0%, #f4f8ff 100%);
}

.faq-help-copy {
    display: flex;
    align-items: center;
    gap: 17px;
}

.faq-help-icon {
    flex: 0 0 49px;
    display: grid;
    place-items: center;
    width: 49px;
    height: 49px;
    border-radius: 15px;
    background: linear-gradient(135deg, #1597d4, #45cbd8);
    color: #fff;
    font-size: 21px;
    box-shadow: 0 8px 18px rgba(21,151,212,.2);
}

.faq-help-copy h3 {
    margin: 0 0 5px;
    color: #08243a;
    font-size: 17px;
    font-weight: 800;
}

.faq-help-copy p {
    margin: 0;
    color: #6b7d8d;
    font-size: 13px;
    line-height: 1.7;
}

.faq-help-btn {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 12px 18px;
    border: 0;
    border-radius: 12px;
    background: linear-gradient(110deg, #087eae, #1597d4);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 7px 18px rgba(21,151,212,.2);
    transition: transform .2s, box-shadow .2s;
}

.faq-help-btn:hover {
    transform: translateY(-2px);
    color: #fff;
    box-shadow: 0 10px 22px rgba(21,151,212,.28);
}

/* Mobile layout */
@media (max-width: 767px) {
    body > main.faq-page {
        padding-bottom: 40px !important;
    }

    body > main.faq-page > .faq-hero {
        padding: 18px 0 24px !important;
        border-radius: 0 0 22px 22px;
    }

    .faq-hero h1 {
        font-size: clamp(26px, 7vw, 34px) !important;
        letter-spacing: -.8px;
    }

    .faq-hero-description {
        padding: 0 7px;
        font-size: 13px;
    }

    .faq-search-wrap {
        margin-top: 17px !important;
    }

    .faq-search-input {
        min-height: 49px;
    }

    .faq-content {
        padding-top: 22px !important;
    }

    .faq-page .accordion-button {
        gap: 11px;
        padding: 15px 14px;
        font-size: 14px;
    }

    .faq-number {
        flex-basis: 31px;
        width: 31px;
        height: 31px;
        border-radius: 9px;
    }

    .faq-page .accordion-body {
        padding: 0 16px 18px 56px;
        font-size: 13px;
    }

    .faq-help-panel {
        align-items: flex-start;
        flex-direction: column;
        padding: 21px 18px;
    }

    .faq-help-btn {
        width: 100%;
    }
}

@media (prefers-reduced-motion: reduce) {
    .faq-page *,
    .faq-page *::before,
    .faq-page *::after {
        transition: none !important;
        animation: none !important;
    }
}



</style>

<section class="faq-page">

    `
<section class="faq-hero" style="
    display: block !important;
    position: relative !important;
    width: calc(100% - 48px) !important;
    max-width: 1140px !important;
    min-height: 0 !important;
    margin: 0 auto 0 !important;
    padding: 36px 24px 38px !important;
    background: linear-gradient(
        125deg,
        #08243a 0%,
        #103e5a 56%,
        #126c89 100%
    ) !important;
    border: 1px solid rgba(69, 213, 223, 0.24) !important;
    border-radius: 24px !important;
    box-shadow: 0 14px 35px rgba(8, 36, 58, 0.13) !important;
    color: #fff !important;
    box-sizing: border-box !important;
    overflow: hidden !important;
">

        <div class="container">
            <div class="faq-hero-content">

                <span class="faq-kicker">
                    <span class="faq-kicker-dot"></span>
                    PATIENT SUPPORT CENTER
                </span>

                <h1>
                    Frequently Asked <span>Questions</span>
                </h1>

                <p class="faq-hero-description">
                    Everything you need to know about appointments,
                    doctors and our clinic services — all in one place.
                </p>

                <div class="faq-search-wrap">
                    <span class="faq-search-icon" aria-hidden="true">
                        &#9906;
                    </span>

                    <input
                        type="search"
                        id="faqSearch"
                        class="faq-search-input"
                        placeholder="Search questions or topics..."
                        aria-label="Search frequently asked questions"
                        autocomplete="off"
                    >

                    <button
                        type="button"
                        id="faqSearchClear"
                        class="faq-search-clear"
                        aria-label="Clear search"
                    >&times;</button>
                </div>

            </div>
        </div>
    </section>

    <section class="container">
        <div class="faq-content">

            <div class="faq-section-heading">
                <span class="small-label">HELPFUL INFORMATION</span>
                <h2>How can we help you?</h2>
                <p>Browse the answers below or search for a specific question.</p>
            </div>

            <div
                id="faqResultsStatus"
                class="faq-results-status"
                aria-live="polite"
            ></div>

            <div class="faq-list accordion" id="faq">

                <?php if (!empty($faqs)): ?>

                    <?php foreach ($faqs as $i => $f): ?>
                        <?php
                            $faqId = (int) $f['id'];
                            $collapseId = 'faq-answer-' . $faqId;
                            $isFirst = ($i === 0);
                        ?>

                        <div class="accordion-item faq-item">
                            <h2
                                class="accordion-header"
                                id="faq-heading-<?= $faqId ?>"
                            >
                                <button
                                    class="accordion-button <?= $isFirst ? '' : 'collapsed' ?>"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#<?= e($collapseId) ?>"
                                    aria-expanded="<?= $isFirst ? 'true' : 'false' ?>"
                                    aria-controls="<?= e($collapseId) ?>"
                                >
                                    <span class="faq-number">
                                        <?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?>
                                    </span>

                                    <span><?= e($f['question']) ?></span>
                                </button>
                            </h2>

                            <div
                                id="<?= e($collapseId) ?>"
                                class="accordion-collapse collapse <?= $isFirst ? 'show' : '' ?>"
                                aria-labelledby="faq-heading-<?= $faqId ?>"
                                data-bs-parent="#faq"
                            >
                                <div class="accordion-body">
                                    <div class="faq-answer-text">
                                        <?= nl2br(e($f['answer'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="faq-empty-state visible">
                        <div class="faq-empty-icon" aria-hidden="true">&#128269;</div>
                        <h3>No FAQs available yet</h3>
                        <p>Please check back later for helpful answers.</p>
                    </div>

                <?php endif; ?>

            </div>

            <div class="faq-empty-state" id="faqEmptyState">
                <div class="faq-empty-icon" aria-hidden="true">&#128269;</div>
                <h3>No matching questions found</h3>
                <p>Try another keyword or clear your search to see all FAQs.</p>
            </div>

            <div class="faq-help-panel">
                <div class="faq-help-copy">
                    <div class="faq-help-icon" aria-hidden="true">
                        <span>&#9993;</span>
                    </div>

                    <div>
                        <h3>Still have questions?</h3>
                        <p>Our clinic team is here to help you find the information you need.</p>
                    </div>
                </div>

                <a href="<?= e($base ?? '') ?>contact.php" class="faq-help-btn">
                    Contact Us <span aria-hidden="true">&rarr;</span>
                </a>
            </div>

        </div>
    </section>

</section>

<script>
(function () {
    const searchInput = document.getElementById('faqSearch');
    const clearButton = document.getElementById('faqSearchClear');
    const faqItems = Array.from(document.querySelectorAll('.faq-item'));
    const emptyState = document.getElementById('faqEmptyState');
    const resultsStatus = document.getElementById('faqResultsStatus');

    if (!searchInput || !clearButton || !emptyState || !resultsStatus) {
        return;
    }

    function filterFAQs() {
        const query = searchInput.value.trim().toLocaleLowerCase();
        let visibleCount = 0;

        faqItems.forEach(function (item) {
            const matches = item.innerText.toLocaleLowerCase().includes(query);

            item.style.display = matches ? '' : 'none';

            if (matches) {
                visibleCount++;
            } else {
                const openPanel = item.querySelector('.accordion-collapse.show');

                if (openPanel && window.bootstrap) {
                    const instance = window.bootstrap.Collapse.getInstance(openPanel);

                    if (instance) {
                        instance.hide();
                    }
                }
            }
        });

        emptyState.classList.toggle(
            'visible',
            faqItems.length > 0 && visibleCount === 0
        );

        clearButton.classList.toggle('visible', query.length > 0);

        if (query.length > 0) {
            resultsStatus.textContent =
                visibleCount + (
                    visibleCount === 1
                        ? ' matching question found'
                        : ' matching questions found'
                );
        } else {
            resultsStatus.textContent = faqItems.length
                ? faqItems.length + (
                    faqItems.length === 1
                        ? ' frequently asked question'
                        : ' frequently asked questions'
                )
                : '';
        }
    }

    searchInput.addEventListener('input', filterFAQs);

    clearButton.addEventListener('click', function () {
        searchInput.value = '';
        filterFAQs();
        searchInput.focus();
    });

    filterFAQs();
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
