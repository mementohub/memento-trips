{{-- CSS/JS assets for mobile bottom navigation --}}
<style>@media (max-width: 991.98px) {
  :root {
    --app-accent: #ff4200;
    --app-text: #0f172a;
    --app-muted: #64748b;
    --app-bg: #fff;
    --app-border: rgba(15, 23, 42, .10);
    --app-shadow: 0 -12px 30px rgba(15, 23, 42, .10);
    --app-radius: 18px;
    --app-bar-h: 68px;
  }

  .tg-header__area {
    display: none !important;
  }


  body {
    padding-bottom: calc(var(--app-bar-h) + env(safe-area-inset-bottom, 0px) + 12px) !important;
  }

  .app-bottom-bar {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    height: calc(var(--app-bar-h) + env(safe-area-inset-bottom, 0px));
    padding: 10px 10px calc(10px + env(safe-area-inset-bottom, 0px));
    background: var(--app-bg);
    border-top: 1px solid var(--app-border);
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    /* DEFAULT: 5 */
    gap: 6px;
    z-index: 9999;
  }

  .app-bottom-bar--3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .app-bottom-bar--5 {
    grid-template-columns: repeat(5, minmax(0, 1fr));
  }

  .app-bottom-item {
    border: 0;
    background: transparent;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    text-decoration: none !important;
    color: var(--app-muted) !important;
    font-weight: 700;
    padding: 8px 6px;
    border-radius: 14px;
    line-height: 1;
  }

  .app-bottom-item--btn {
    cursor: pointer;
  }

  .app-bottom-ico {
    width: 36px;
    height: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: currentColor;
  }

  .app-bottom-txt {
    font-size: 11px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
  }

  .app-bottom-item.is-active {
    color: var(--app-accent) !important;
    background: rgba(255, 66, 0, .08);
  }

  .app-nav-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .35);
    opacity: 0;
    pointer-events: none;
    transition: opacity .18s ease;
    z-index: 9997;
  }

  .app-nav-sheet {
    position: fixed;
    left: 12px;
    right: 12px;
    bottom: calc(var(--app-bar-h) + env(safe-area-inset-bottom, 0px) + 10px);
    background: var(--app-bg);
    border: 1px solid var(--app-border);
    box-shadow: var(--app-shadow);
    border-radius: var(--app-radius);
    transform: translateY(18px);
    opacity: 0;
    pointer-events: none;
    transition: transform .18s ease, opacity .18s ease;
    z-index: 9998;
    max-height: min(62vh, 520px);
    overflow: hidden;
  }

  .app-nav-sheet__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 14px 10px;
    border-bottom: 1px solid var(--app-border);
  }

  .app-nav-user {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
  }

  .app-nav-user__avatar {
    width: 44px;
    height: 44px;
    border-radius: 999px;
    overflow: hidden;
    flex: 0 0 auto;
    border: 1px solid var(--app-border);
    background: #f8fafc;
  }

  .app-nav-user__avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .app-nav-user__meta {
    min-width: 0;
  }

  .app-nav-user__name {
    font-size: 14px;
    font-weight: 800;
    color: var(--app-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .app-nav-user__email {
    font-size: 12px;
    color: var(--app-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .app-nav-sheet__close {
    width: 40px;
    height: 40px;
    border-radius: 14px;
    border: 1px solid var(--app-border);
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    line-height: 1;
    color: var(--app-text);
  }

  .app-nav-sheet__content {
    padding: 12px;
    overflow: auto;
    -webkit-overflow-scrolling: touch;
  }

  .app-nav-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
  }

  .app-nav-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    border-radius: 14px;
    border: 1px solid var(--app-border);
    background: #fff;
    text-decoration: none !important;
    color: var(--app-text) !important;
    font-weight: 700;
    min-height: 48px;
  }

  .app-nav-card__ico {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    background: rgba(255, 66, 0, .10);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--app-accent);
    flex: 0 0 auto;
  }

  .app-nav-card__txt {
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .app-nav-card--danger .app-nav-card__ico {
    background: rgba(220, 38, 38, .10);
    color: #dc2626;
  }

  .app-nav-sheet__footer {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid var(--app-border);
  }

  .app-nav-wide {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px;
    border-radius: 14px;
    border: 1px solid var(--app-border);
    background: rgba(255, 66, 0, .08);
    color: var(--app-accent) !important;
    font-weight: 800;
    text-decoration: none !important;
  }

  body.app-nav-open .app-nav-backdrop {
    opacity: 1;
    pointer-events: auto;
  }

  body.app-nav-open .app-nav-sheet {
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0);
  }
}

.app-bottom-bar {
  padding: 8px 8px calc(8px + env(safe-area-inset-bottom, 0px));
  gap: 4px;
}

.app-bottom-item {
  padding: 6px 6px;
  gap: 4px;
  border-radius: 12px;
}

.app-bottom-ico {
  width: 32px;
  height: 24px;
  font-size: 19px;
}

.app-bottom-txt {
  font-size: 10.5px;
  letter-spacing: .1px;
}

.app-bottom-item.is-active {
  background: rgba(255, 66, 0, .06);
}

.app-bottom-item,
.app-bottom-item--btn {
  min-height: 48px;
}


@media (min-width: 992px) {
  .app-bottom-bar {
    display: none !important;
  }

  body {
    padding-bottom: 0 !important;
  }
}


</style><script>(function() {
    function openNav() {
      document.body.classList.add('app-nav-open');
    }

    function closeNav() {
      document.body.classList.remove('app-nav-open');
    }

    document.addEventListener('click', function(e) {
        // open: butonul din middle (are class app-bottom-item--btn in user partial)
        if(e.target.closest('.app-bottom-item--btn')) openNav();

        // close: backdrop sau X
        if(e.target.closest('.app-nav-backdrop') || e.target.closest('.app-nav-sheet__close')) closeNav();
      });

    document.addEventListener('keydown', function(e) {
        if(e.key==='Escape') closeNav();
      });
  })();
</script>