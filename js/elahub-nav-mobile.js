document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('elahub-mobile-toggle')
  const panel = document.getElementById('elahub-mobile-panel')
  const overlay = document.getElementById('elahub-mobile-overlay')
  const closeButton = panel?.querySelector('[data-elahub-mobile-close]')
  const body = document.body

  if (!toggle || !panel || !overlay) return

  const getFocusable = () => [
    ...panel.querySelectorAll(
      'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'
    )
  ]

  const setOpenState = isOpen => {
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false')
    panel.setAttribute('aria-hidden', isOpen ? 'false' : 'true')
    overlay.classList.toggle('hidden', !isOpen)
    panel.classList.toggle('translate-x-full', !isOpen)
    panel.classList.toggle('translate-x-0', isOpen)
    // overflow-y-hidden prevents page scroll when the menu is open without
    // overriding the global overflow-x:clip that keeps off-canvas content hidden.
    body.classList.toggle('overflow-y-hidden', isOpen)
  }

  const closeAllSubmenus = () => {
    panel.querySelectorAll('[data-elahub-mobile-subtoggle]').forEach(btn => {
      const id = btn.getAttribute('aria-controls')
      const submenu = id ? document.getElementById(id) : null
      const icon = btn.querySelector('.elahub-mobile-chevron')
      btn.setAttribute('aria-expanded', 'false')
      submenu?.classList.add('hidden')
      icon?.classList.remove('rotate-180')
    })
  }

  const openPanel = () => {
    setOpenState(true)
    getFocusable()[0]?.focus()
  }

  const closePanel = ({ returnFocus = true } = {}) => {
    setOpenState(false)
    closeAllSubmenus()
    if (returnFocus) toggle.focus()
  }

  toggle.addEventListener('click', e => {
    e.preventDefault()
    const isOpen = toggle.getAttribute('aria-expanded') === 'true'
    isOpen ? closePanel() : openPanel()
  })

  closeButton?.addEventListener('click', e => {
    e.preventDefault()
    closePanel()
  })

  overlay.addEventListener('click', () => closePanel())

  panel.addEventListener('click', e => {
    const btn = e.target.closest('[data-elahub-mobile-subtoggle]')
    if (!btn) return

    e.preventDefault()

    const id = btn.getAttribute('aria-controls')
    const submenu = id ? document.getElementById(id) : null
    const icon = btn.querySelector('.elahub-mobile-chevron')
    const expanded = btn.getAttribute('aria-expanded') === 'true'

    btn.setAttribute('aria-expanded', expanded ? 'false' : 'true')
    submenu?.classList.toggle('hidden', expanded)
    icon?.classList.toggle('rotate-180', !expanded)
  })

  document.addEventListener('keydown', e => {
    const isOpen = toggle.getAttribute('aria-expanded') === 'true'
    if (!isOpen) return

    if (e.key === 'Escape') {
      e.preventDefault()
      closePanel()
      return
    }

    if (e.key === 'Tab') {
      const focusable = getFocusable()
      if (!focusable.length) return

      const first = focusable[0]
      const last = focusable[focusable.length - 1]

      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault()
        last.focus()
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault()
        first.focus()
      }
    }
  })

  window.addEventListener('resize', () => {
    if (window.innerWidth >= 1475) {
      closePanel({ returnFocus: false })
    }
  })
})
