document.addEventListener('DOMContentLoaded', () => {

  // ── Skip to content — toggle .is-focused so the link is visually shown
  //    for keyboard users regardless of how the browser handles :focus on
  //    off-screen elements.
  const skipLink = document.querySelector('.elahub-skip-link')
  if (skipLink) {
    skipLink.addEventListener('focus', () => skipLink.classList.add('is-focused'))
    skipLink.addEventListener('blur',  () => skipLink.classList.remove('is-focused'))
  }

  const overlay = document.getElementById('elahub-desktop-overlay')
  const headerRow = document.querySelector('.header-row')
  const nav = document.getElementById('site-navigation')

  if (!nav) return

  const buttons = Array.from(nav.querySelectorAll('[data-elahub-menu-toggle]'))
  let activeButton = null
  let ignoreOutsideClickUntil = 0

  const getPanel = (button) => {
    const panelId = button?.getAttribute('aria-controls')
    return panelId ? document.getElementById(panelId) : null
  }

  const setOverlayTop = () => {
    if (!overlay) return
    const top = headerRow ? headerRow.getBoundingClientRect().bottom + window.scrollY : 0
    overlay.style.top = `${top}px`
  }

  const closeAll = (returnFocus = false) => {
    buttons.forEach((button) => {
      button.setAttribute('aria-expanded', 'false')
      const panel = getPanel(button)
      if (panel) panel.classList.add('hidden')
    })

    if (overlay) overlay.classList.add('hidden')

    // Re-enable page scroll when all submenus close
    document.documentElement.style.overflowY = ''
    document.body.style.overflowY = ''

    if (returnFocus && activeButton) {
      activeButton.focus()
    }

    activeButton = null
  }

  const openPanel = (button) => {
    const panel = getPanel(button)
    if (!panel) return

    buttons.forEach((otherButton) => {
      const otherPanel = getPanel(otherButton)
      const isActive = otherButton === button
      otherButton.setAttribute('aria-expanded', isActive ? 'true' : 'false')
      if (otherPanel) {
        otherPanel.classList.toggle('hidden', !isActive)
      }
    })

    activeButton = button
    ignoreOutsideClickUntil = Date.now() + 150

    if (overlay) {
      setOverlayTop()
      overlay.classList.remove('hidden')
    }

    // Prevent page scroll while a submenu is open.
    // Set on both html and body so it works across all browsers.
    document.documentElement.style.overflowY = 'hidden'
    document.body.style.overflowY = 'hidden'
  }

  buttons.forEach((button) => {
    button.addEventListener('click', (event) => {
      event.preventDefault()
      event.stopPropagation()

      const isExpanded = button.getAttribute('aria-expanded') === 'true'

      if (isExpanded) {
        activeButton = button
        closeAll(true)
        return
      }

      openPanel(button)
    })

    button.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowDown') {
        event.preventDefault()
        openPanel(button)
        const panel = getPanel(button)
        const firstFocusable = panel?.querySelector('a, button, [tabindex]:not([tabindex="-1"])')
        if (firstFocusable) firstFocusable.focus()
      }

      if (event.key === 'Escape') {
        event.preventDefault()
        activeButton = button
        closeAll(true)
      }
    })
  })

  if (overlay) {
    overlay.addEventListener('click', () => closeAll(false))
  }

  document.addEventListener('click', (event) => {
    if (Date.now() < ignoreOutsideClickUntil) return

    if (event.target.closest('#site-navigation')) return
    if (event.target.closest('.elahub-submenu')) return

    closeAll(false)
  })

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeAll(true)
    }
  })

  window.addEventListener('resize', () => {
    if (window.innerWidth < 1475) {
      closeAll(false)
      return
    }

    const hasOpenPanel = buttons.some((button) => button.getAttribute('aria-expanded') === 'true')
    if (hasOpenPanel) setOverlayTop()
  })
})
