const Theme = (() => {
  'use strict'

  // Debounced resize event (width only). [ref: https://paulbrowne.xyz/debouncing]
  function resize(a, b) {
    const c = [window.innerWidth]
    return window.addEventListener('resize', () => {
      const e = c.length
      c.push(window.innerWidth)
      if (c[e] !== c[e - 1]) {
        clearTimeout(b)
        b = setTimeout(a, 150)
      }
    }), a
  }

  // Bootstrap breakPoint checker
  function breakPoint(value) {
    let el, check, cls

    switch (value) {
      case 'xs': cls = 'd-none d-sm-block'; break
      case 'sm': cls = 'd-block d-sm-none d-md-block'; break
      case 'md': cls = 'd-block d-md-none d-lg-block'; break
      case 'lg': cls = 'd-block d-lg-none d-xl-block'; break
      case 'xl': cls = 'd-block d-xl-none'; break
      case 'smDown': cls = 'd-none d-md-block'; break
      case 'mdDown': cls = 'd-none d-lg-block'; break
      case 'lgDown': cls = 'd-none d-xl-block'; break
      case 'smUp': cls = 'd-block d-sm-none'; break
      case 'mdUp': cls = 'd-block d-md-none'; break
      case 'lgUp': cls = 'd-block d-lg-none'; break
    }

    el = document.createElement('div')
    el.setAttribute('class', cls)
    document.body.appendChild(el)
    check = el.offsetParent === null
    el.parentNode.removeChild(el)

    return check
  }

  // Shorthand for Bootstrap breakPoint checker
  function xs() { return breakPoint('xs') }
  function sm() { return breakPoint('sm') }
  function md() { return breakPoint('md') }
  function lg() { return breakPoint('lg') }
  function xl() { return breakPoint('xl') }
  function smDown() { return breakPoint('smDown') }
  function mdDown() { return breakPoint('mdDown') }
  function lgDown() { return breakPoint('lgDown') }
  function smUp() { return breakPoint('smUp') }
  function mdUp() { return breakPoint('mdUp') }
  function lgUp() { return breakPoint('lgUp') }

  // https://css-tricks.com/the-trick-to-viewport-units-on-mobile/
  let vh = window.innerHeight * 0.01
  document.documentElement.style.setProperty('--vh', `${vh}px`)
  window.addEventListener('resize', () => {
    let vh = window.innerHeight * 0.01
    document.documentElement.style.setProperty('--vh', `${vh}px`)
  })

  // Tree view
  function treeview() {
    document.addEventListener('click', e => {
      if (e.target.closest('.treeview-toggle')) {
        const toggler = e.target.closest('.treeview-toggle')
        const ulParent = toggler.closest('ul')
        const ulTop = toggler.closest('.treeview')
        if (typeof ulParent.dataset.accordion != 'undefined') {
          ulParent.querySelectorAll(':scope > li > .show').forEach(i => toggler != i && i.classList.remove('show'))
        }
        toggler.classList.toggle('show')
        const eventName = toggler.classList.contains('show') ? 'treeview:shown' : 'treeview:hidden'
        toggler.dispatchEvent(new Event(eventName))
        ulTop.dispatchEvent(new Event('treeview:updated'))
        e.preventDefault()
      }
    })
  }

  // Toggle sidebar collapse or expand
  function toggleSidebar() {
    document.addEventListener('click', e => {
      if (e.target.closest('[data-toggle="sidebar"]')) {
        lgUp() ? document.body.classList.toggle('sidebar-collapse') : document.body.classList.toggle('sidebar-expand')
        document.querySelector('.sidebar-body').scrollTop = 0
        window.dispatchEvent(new Event('resize'))
        e.preventDefault()
      }
    })

    void function () {
      // Insert sidebar backdrop
      document.body.insertAdjacentHTML('beforeend', '<div class="sidebar-backdrop" id="sidebarBackdrop" data-toggle="sidebar"></div>')

      // Remember sidebar scroll position
      const sidebar = document.querySelector('.sidebar')
      if (sidebar) {
        const sidebarBody = sidebar.querySelector('.sidebar-body')
        let bodyClass = document.body.classList
        let scrollPosition = 0
        let lock = false
        sidebarBody.addEventListener('scroll', function () {
          !lock && (scrollPosition = this.scrollTop) // save last scroll
        })
        document.addEventListener('click', e => {
          if (e.target.closest('[data-toggle="sidebar"]')) {
            if (!bodyClass.contains('sidebar-collapse') || bodyClass.contains('sidebar-expand')) {
              sidebarBody.scrollTop = scrollPosition // restore position on expanded
            }
          }
        })
        sidebar.addEventListener('mouseenter', () => {
          if (bodyClass.contains('sidebar-collapse') && lgUp()) {
            lock = false
            sidebarBody.scrollTop = scrollPosition // restore on hover
          }
        })
        sidebar.addEventListener('mouseleave', () => {
          if (bodyClass.contains('sidebar-collapse') && lgUp()) {
            lock = true
            sidebarBody.scrollTop = 0 // reset on unhover
          }
        })
      }
    }()
  }

  // Custom scrollbar for sidebar
  function sidebarBodyCustomScrollBar() {
    new SimpleBar(document.querySelector('.sidebar .sidebar-body'))
  }

  // Focus to modal content who has 'autofocus' attribute
  function autofocusModal() {
    $(document).on('shown.bs.modal', '.modal', function () {
      const autofocusEl = this.querySelector('[autofocus]')
      autofocusEl && autofocusEl.focus()
    })
  }

  // Show filename for bootstrap custom file input
  function customFileInput() {
    document.addEventListener('change', e => {
      if (e.target.closest('.custom-file-input')) {
        const el = e.target.closest('.custom-file-input')
        const chooseText = el.dataset.choose ? el.dataset.choose : el.nextElementSibling.innerText
        !el.dataset.choose && (el.dataset.choose = chooseText)
        const fileLength = el.files.length
        let filename = fileLength ? el.files[0].name : chooseText
        filename = fileLength > 1 ? fileLength + ' files' : filename // if more than one file, show '{amount} files'
        el.parentElement.querySelector('label').textContent = filename
      }
    })
  }

  // Functional card toolbar
  function cardToolbar() {
    document.addEventListener('click', e => {
      if (e.target.closest('[data-action]')) {
        const el = e.target.closest('[data-action]')
        const card = el.closest('.card')
        switch (el.dataset.action) {
          case 'fullscreen':
            card.classList.toggle('card-fullscreen')
            if (card.classList.contains('card-fullscreen')) {
              el.innerHTML = '<i class="material-icons">fullscreen_exit</i>'
              document.body.style.overflow = 'hidden'
            } else {
              el.innerHTML = '<i class="material-icons">fullscreen</i>'
              document.body.removeAttribute('style')
            }
            break;
          case 'close':
            card.remove()
            break;
          case 'reload':
            card.insertAdjacentHTML('afterbegin', '<div class="card-loader-overlay"><div class="spinner-border text-white" role="status"></div></div>')
            card.dispatchEvent(new Event('card:reload'))
            break;
          case 'collapse':
            const collapsingTransition = parseFloat(getComputedStyle(document.querySelector('.collapsing'))['transitionDuration']) * 1000
            setTimeout(() => {
              if (document.querySelector(el.dataset.target).matches('.collapse.show')) {
                el.innerHTML = '<i class="material-icons">remove</i>'
              } else {
                el.innerHTML = '<i class="material-icons">add</i>'
              }
            }, collapsingTransition);
            break;
        }
      }
    })
  }

  // Nav section
  function navSection() {
    if (document.querySelector('#navSection')) {
      $('body').scrollspy('dispose')
      $('body').scrollspy({
        target: '#navSection',
        offset: 140,
      })
    }
    document.addEventListener('click', e => {
      if (e.target.closest('#navSection')) {
        const target = document.querySelector(e.target.getAttribute('href'))
        const y = target.getBoundingClientRect().top + window.pageYOffset - ((document.body.dataset.offset || 140) - 1)
        window.scrollTo({ top: y, behavior: 'smooth' })
        e.preventDefault()
      }
    })
  }

  // Set accordion active card
  function accordionActive() {
    $('.collapse.show[data-parent]').closest('.card').addClass('active')
    $(document)
      .on('show.bs.collapse', '.collapse[data-parent]', function () {
        $(this).closest('.card').addClass('active')
      })
      .on('hide.bs.collapse', '.collapse[data-parent]', function () {
        $(this).closest('.card').removeClass('active')
      })
  }

  // Dropdown hover
  function dropdownHover() {
    document.addEventListener('mouseover', e => {
      if (lgUp()) {
        if (e.target.closest('.dropdown-hover')) {
          $('.dropdown-hover').removeClass('show')
          e.target.closest('.dropdown-hover').classList.add('show')
        } else {
          $('.dropdown-hover').removeClass('show')
        }
      }
    })
  }

  // Table with check all & bulk action
  function checkAll() {
    if (document.querySelectorAll('.has-checkAll').length) {
      const activeTr= 'table-active'
      for (const el of document.querySelectorAll('.has-checkAll')) {
        const thCheckbox = el.querySelector('th input[type="checkbox"]')
        const tdCheckbox = el.querySelectorAll('tr > td:first-child input[type="checkbox"]')
        const bulkTarget = el.dataset.bulkTarget
        let activeClass = el.dataset.checkedClass
        activeClass = activeClass ? activeClass : activeTr
        thCheckbox.addEventListener('click', function () {
          for (const cb of tdCheckbox) {
            cb.checked = this.checked
            cb.checked ? cb.closest('tr').classList.add(activeClass) : cb.closest('tr').classList.remove(activeClass)
          }
          bulkTarget && toggleBulkDropdown(bulkTarget, tdCheckbox)
        })
        for (const cb of tdCheckbox) {
          cb.addEventListener('click', function () {
            this.checked ? this.closest('tr').classList.add(activeClass) : this.closest('tr').classList.remove(activeClass)
            bulkTarget && toggleBulkDropdown(bulkTarget, tdCheckbox)
          })
        }
      }
      function toggleBulkDropdown(el, tdCheckbox) {
        let count = 0
        const bulk_dropdown = document.querySelector(el)
        tdCheckbox.forEach(cb => cb.checked && count++)
        bulk_dropdown.querySelector('.checked-counter') && (bulk_dropdown.querySelector('.checked-counter').textContent = count)
        count != 0 ? bulk_dropdown.removeAttribute('hidden') : bulk_dropdown.setAttribute('hidden', '')
      }
    }
  }

  // Background cover
  function backgroundCover() {
    document.querySelectorAll('[data-cover]').forEach(el => el.style.backgroundImage = `url(${el.dataset.cover})`)
  }

  // Toggle inner sidebar
  function innerToggleSidebar() {
    document.addEventListener('click', e => {
      if (e.target.closest('[data-toggle="inner-sidebar"]')) {
        const el = e.target.closest('[data-toggle="inner-sidebar"]')
        const body = document.body
        body.classList.toggle('inner-expand')
        if (body.classList.contains('inner-expand')) {
          el.innerHTML = '<i class="material-icons">close</i>'
        } else {
          el.innerHTML = '<i class="material-icons">arrow_forward_ios</i>'
        }
        e.preventDefault()
      }
    })
  }

  // Scrolling navbar
  function scrollNavbar() {
    if (document.querySelector('.main-navbar')) {
      const navbar = document.querySelector('.main-navbar .navbar-collapse')
      setTimeout(() => {
        resize(() => {
          if (lgUp()) {
            for (const el of document.querySelectorAll('[data-scroll]')) {
              if (navbar.querySelector('.navbar-nav').getBoundingClientRect().width > navbar.getBoundingClientRect().width) {
                el.removeAttribute('hidden')
              } else {
                el.setAttribute('hidden', '')
              }
            }
          }
        })()
      }, 500)
      for (const el of document.querySelectorAll('[data-scroll]')) {
        el.addEventListener('click', e => {
          let width = navbar.getBoundingClientRect().width / 2
          switch (el.dataset.scroll) {
            case 'left':
              navbar.scrollLeft -= width
              break;
            case 'right':
              navbar.scrollLeft += width
              break;
          }
          e.preventDefault()
        })
      }

      // fix dropdown-menu position
      $('.main-navbar .dropdown').on('show.bs.dropdown', function () {
        let margin = document.querySelector('.main-navbar .navbar-collapse').scrollLeft
        this.querySelector('.dropdown-menu').style.marginLeft = -margin + 'px'
      })
    }
  }

  // Feather icon
  function featherIcon() {
    feather.replace()
    /*
    const observer = new MutationObserver(() => feather.replace())
    observer.observe(document.querySelector('.main'), { childList: true, subtree: true, })
    observer.observe(document.querySelector('.sidebar'), { childList: true, subtree: true, })
    */
  }

  // Togle Todo item done
  function todo() {
    document.addEventListener('click', e => {
      if (e.target.closest('[data-toggle="todo-item"]')) {
        const el = e.target.closest('[data-toggle="todo-item"]')
        const ti = el.closest('.todo-item')
        el.checked ? ti.classList.add('done') : ti.classList.remove('done')
      }
    })
  }

  // Fix flatpickr year scroll
  function fixFlatpickr() {
    document.addEventListener('wheel', function () {
      if (document.activeElement.classList.contains('cur-year')) {
        document.activeElement.blur()
      }
    })
  }

  // Add summernote focus class onFocus
  function summernoteFocus() {
    $(document).on('summernote.focus', '.summernote', function () {
      $(this).next().addClass('focus')
    }).on('summernote.blur', '.summernote', function () {
      $(this).next().removeClass('focus')
    })
  }

  // Toast
  function toast(option) {
    const animation = option.animation !== undefined ? option.animation : 'true'
    const autohide = option.autohide !== undefined ? option.autohide : 'true'
    const position = option.position !== undefined ? option.position : 'top-right'
    const wrapper = '.toast-wrapper.' + position
    const delay = option.delay !== undefined ? option.delay : 2000
    const id = 'toast' + Date.now()

    // Icon
    const iconSuccess = '<svg class="mr-2 text-success" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="21" height="21"><path d="M0 0h24v24H0z" fill="none"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>'
    const iconWarning = '<svg class="mr-2 text-warning" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="21" height="21"><path d="M0 0h24v24H0z" fill="none"/><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>'
    const iconError = '<svg class="mr-2 text-danger" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="21" height="21"><path d="M0 0h24v24H0z" fill="none"/><path d="M0 0h24v24H0z" fill="none"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>'
    const iconInfo = '<svg class="mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="21" height="21"><path d="M0 0h24v24H0z" fill="none"></path><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"></path></svg>'
    let icon = ''
    switch (option.icon) {
      case 'success': icon = iconSuccess; break;
      case 'warning': icon = iconWarning; break;
      case 'error': icon = iconError; break;
      default: icon = iconInfo; break;
    }

    const toast = `
      <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" id="${id}" data-autohide="${autohide}" data-animation="${animation}" data-delay="${delay}">
        <div class="toast-header">
          ${icon}
          <strong>${option.title}</strong>
          <button type="button" class="close ml-auto" data-dismiss="toast" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="toast-body">${option.text}</div>
      </div>
    `

    if (!document.querySelector(wrapper)) {
      document.body.insertAdjacentHTML('beforeend', `<div class="toast-wrapper ${position}"></div>`)
    }
    document.querySelector(wrapper).insertAdjacentHTML('beforeend', toast)
    $('#' + id).toast('show')
    $('#' + id).on('hidden.bs.toast', function () {
      this.remove()
      if (document.querySelectorAll(wrapper + ' .toast').length < 1) {
        document.querySelector(wrapper).remove()
      }
    })
  }

  // Auto width input
  function autowidth() {
    if (document.querySelectorAll('.autowidth-hidden').length) {
      document.querySelectorAll('.autowidth-hidden').forEach(el => el.remove())
    }
    function setWidth(el, fakeEl) {
      const string = el.value || el.getAttribute('placeholder') || ''
      fakeEl.innerHTML = string.replace(/ /g, '&nbsp;')
      el.style.setProperty('width', Math.ceil(window.getComputedStyle(fakeEl).width.replace('px', '')) + 1 + 'px', 'important')
    }
    for (const el of document.querySelectorAll('.autowidth')) {
      const fakeEl = document.createElement('div')
      fakeEl.classList.add('autowidth-hidden')
      const styles = window.getComputedStyle(el)
      fakeEl.style.fontFamily = styles.fontFamily
      fakeEl.style.fontSize = styles.fontSize
      fakeEl.style.fontStyle = styles.fontStyle
      fakeEl.style.fontWeight = styles.fontWeight
      fakeEl.style.letterSpacing = styles.letterSpacing
      fakeEl.style.textTransform = styles.textTransform
      fakeEl.style.borderLeftWidth = styles.borderLeftWidth
      fakeEl.style.borderRightWidth = styles.borderRightWidth
      fakeEl.style.paddingLeft = styles.paddingLeft
      fakeEl.style.paddingRight = styles.paddingRight
      document.body.appendChild(fakeEl)
      setWidth(el, fakeEl)
      if (el.classList.contains('inputmask')) {
        el.oninput = () => setWidth(el, fakeEl)
      } else {
        el.addEventListener('input', () => setWidth(el, fakeEl))
      }
    }
  }

  // Toggle password visibility
  function togglePassword() {
    document.addEventListener('click', e => {
      if (e.target.closest('[data-toggle="password"]')) {
        const input = e.target.closest('[data-toggle="password"]').parentNode.querySelector('input')
        input.type = input.type === 'password' ? 'text' : 'password'
      }
    })
  }

  // Bootstrap-select
  function bootstrapSelect() {
    // Toggle 'focus' class
    $(document).on('show.bs.select', '.bootstrap-select', function () {
      this.querySelector('.dropdown-toggle').classList.add('focus')
    }).on('hide.bs.select', '.bootstrap-select', function () {
      this.querySelector('.dropdown-toggle').classList.remove('focus')
    })

    function toggleClear(select, el) {
      el.style.display = select.value == '' ? 'none' : 'inline'
      const optionText = select.parentNode.querySelector('.filter-option')
      select.value == '' ? optionText.classList.remove('mr-4') : optionText.classList.add('mr-4')
    }

    for (const el of document.querySelectorAll('select.bs-select')) {
      let config = { style: 'btn' }

      // creatable
      if (el.dataset.bsSelectCreatable === 'true') {
        config.liveSearch = true
        config.noneResultsText = 'Press Enter to add: <b>{0}</b>'
      }
      // sizing
      if (el.dataset.bsSelectSize) {
        config.style = 'btn btn-' + el.dataset.bsSelectSize
        el.classList.add('form-control-' + el.dataset.bsSelectSize)
      }
      // clearable
      if (el.dataset.bsSelectClearable === 'true') {
        el.insertAdjacentHTML('afterend', '<span class="bs-select-clear"></span>')
      }

      // run
      $(el).selectpicker(config)

      const bs = el.closest('.bootstrap-select')

      // creatable
      if (el.dataset.bsSelectCreatable === 'true') {
        const bsInput = bs.querySelector('.bs-searchbox .form-control')
        bsInput.addEventListener('keyup', function (e) {
          if (bs.querySelector('.no-results')) {
            if (e.keyCode === 13) {
              el.insertAdjacentHTML('afterbegin', `<option value="${this.value}">${this.value}</option>`)
              let newVal = $(el).val()
              Array.isArray(newVal) ? newVal.push(this.value) : newVal = this.value
              $(el).val(newVal)
              $(el).selectpicker('toggle')
              $(el).selectpicker('refresh')
              $(el).selectpicker('render')
              bs.querySelector('.dropdown-toggle').focus()
              this.value = ''
            }
          }
        })
      }

      // clearable
      const clearEl = el.parentNode.nextElementSibling
      if (clearEl && clearEl.classList.contains('bs-select-clear')) {
        toggleClear(el, clearEl)
        el.addEventListener('change', () => toggleClear(el, clearEl))
        clearEl.addEventListener('click', () => {
          $(el).selectpicker('val', '')
          el.dispatchEvent(new Event('change'))
        })
      }
    }
  }

  // Select2
  function select2() {
    for (const el of document.querySelectorAll('.select2')) {
      let config = {
        width: '100%',
        minimumResultsForSearch: 'Infinity', // hide search
      }

      // live search
      if (el.dataset.select2Search) {
        if (el.dataset.select2Search === 'true') {
          delete config.minimumResultsForSearch
        }
      }

      // custom content
      if (el.dataset.select2Content) {
        if (el.dataset.select2Content === 'true') {
          config.templateResult = state => state.id ? $(state.element.dataset.content) : state.text
          config.templateSelection = state => state.id ? $(state.element.dataset.content) : state.text
        }
      }

      // run
      $(el).select2(config).on('select2:unselecting', function () {
        $(this).data('unselecting', true)
      }).on('select2:opening', function (e) {
        if ($(this).data('unselecting')) {
          $(this).removeData('unselecting')
          e.preventDefault()
        }
      })
    }
  }

  // Input with clear icon
  function inputClearable() {
    document.addEventListener('click', e => {
      if (e.target.closest('[data-toggle="clear"]')) {
        e.target.closest('[data-toggle="clear"]').previousElementSibling.value = ''
      }
    })
  }

  return {
    resize: callback => resize(callback),
    xs: () => xs(),
    sm: () => sm(),
    md: () => md(),
    lg: () => lg(),
    xl: () => xl(),
    smDown: () => smDown(),
    mdDown: () => mdDown(),
    lgDown: () => lgDown(),
    smUp: () => smUp(),
    mdUp: () => mdUp(),
    lgUp: () => lgUp(),
    treeview: () => treeview(),
    toggleSidebar: () => toggleSidebar(),
    sidebarBodyCustomScrollBar: () => sidebarBodyCustomScrollBar(),
    autofocusModal: () => autofocusModal(),
    color: variant => getComputedStyle(document.body).getPropertyValue('--' + variant).trim(),
    customFileInput: () => customFileInput(),
    cardToolbar: () => cardToolbar(),
    stopCardLoader: card => {
      let overlay = card.querySelector('.card-loader-overlay')
      overlay.parentNode.removeChild(overlay)
    },
    navSection: () => navSection(),
    accordionActive: () => accordionActive(),
    dropdownHover: () => dropdownHover(),
    checkAll: () => checkAll(),
    backgroundCover: () => backgroundCover(),
    innerToggleSidebar: () => innerToggleSidebar(),
    scrollNavbar: () => scrollNavbar(),
    featherIcon: () => featherIcon(),
    todo: () => todo(),
    fixFlatpickr: () => fixFlatpickr(),
    summernoteFocus: () => summernoteFocus(),
    toast: option => toast(option),
    autowidth: () => autowidth(),
    togglePassword: () => togglePassword(),
    bootstrapSelect: () => bootstrapSelect(),
    select2: () => select2(),
    inputClearable: () => inputClearable(),
  }
})()

$(() => {
  $('[data-toggle="popover"]').popover()
  $('[data-toggle="tooltip"]').tooltip()
  feather.replace()
})

Theme.treeview()
Theme.toggleSidebar()
//Theme.sidebarBodyCustomScrollBar()
Theme.autofocusModal()
Theme.customFileInput()
Theme.cardToolbar()
Theme.navSection()
Theme.accordionActive()
Theme.dropdownHover()
Theme.checkAll()
Theme.backgroundCover()
Theme.innerToggleSidebar()
Theme.scrollNavbar()
Theme.featherIcon()
Theme.todo()
Theme.fixFlatpickr()
Theme.summernoteFocus()
Theme.togglePassword()
Theme.inputClearable()

const observer = new MutationObserver(() => {
  Theme.backgroundCover()
  Theme.navSection()
  Theme.accordionActive()
  $('[data-toggle="popover"]').popover()
  $('[data-toggle="tooltip"]').tooltip()
})
if (document.querySelector('.main')) {
  observer.observe(document.querySelector('.main'), { childList: true, subtree: true, })
  observer.observe(document.querySelector('.sidebar'), { childList: true, subtree: true, })
}

// Sample colors
const blue   = Theme.color('blue')
const indigo = Theme.color('indigo')
const purple = Theme.color('purple')
const pink   = Theme.color('pink')
const red    = Theme.color('red')
const orange = Theme.color('orange')
const yellow = Theme.color('yellow')
const green  = Theme.color('green')
const teal   = Theme.color('teal')
const cyan   = Theme.color('cyan')
const gray   = Theme.color('gray')
const lime   = '#cddc39'

// This is for development, attach breakpoint to document title
/* App.resize(() => {
  if (App.xs()) { document.title = 'xs' }
  if (App.sm()) { document.title = 'sm' }
  if (App.md()) { document.title = 'md' }
  if (App.lg()) { document.title = 'lg' }
  if (App.xl()) { document.title = 'xl' }
})() */
;/*
 * Toastr
 * Copyright 2012-2015
 * Authors: John Papa, Hans Fjällemark, and Tim Ferrell.
 * All Rights Reserved.
 * Use, reproduction, distribution, and modification of this code is subject to the terms and
 * conditions of the MIT license, available at http://www.opensource.org/licenses/mit-license.php
 *
 * ARIA Support: Greta Krafsig
 *
 * Project: https://github.com/CodeSeven/toastr
 */
/* global define */
(function (define) {
    define(['jquery'], function ($) {
        return (function () {
            var $container;
            var listener;
            var toastId = 0;
            var toastType = {
                error: 'error',
                info: 'info',
                success: 'success',
                warning: 'warning'
            };

            var toastr = {
                clear: clear,
                remove: remove,
                error: error,
                getContainer: getContainer,
                info: info,
                options: {},
                subscribe: subscribe,
                success: success,
                version: '2.1.4',
                warning: warning
            };

            var previousToast;

            return toastr;

            ////////////////

            function error(message, title, optionsOverride) {
                return notify({
                    type: toastType.error,
                    iconClass: getOptions().iconClasses.error,
                    message: message,
                    optionsOverride: optionsOverride,
                    title: title
                });
            }

            function getContainer(options, create) {
                if (!options) { options = getOptions(); }
                $container = $('#' + options.containerId);
                if ($container.length) {
                    return $container;
                }
                if (create) {
                    $container = createContainer(options);
                }
                return $container;
            }

            function info(message, title, optionsOverride) {
                return notify({
                    type: toastType.info,
                    iconClass: getOptions().iconClasses.info,
                    message: message,
                    optionsOverride: optionsOverride,
                    title: title
                });
            }

            function subscribe(callback) {
                listener = callback;
            }

            function success(message, title, optionsOverride) {
                return notify({
                    type: toastType.success,
                    iconClass: getOptions().iconClasses.success,
                    message: message,
                    optionsOverride: optionsOverride,
                    title: title
                });
            }

            function warning(message, title, optionsOverride) {
                return notify({
                    type: toastType.warning,
                    iconClass: getOptions().iconClasses.warning,
                    message: message,
                    optionsOverride: optionsOverride,
                    title: title
                });
            }

            function clear($toastElement, clearOptions) {
                var options = getOptions();
                if (!$container) { getContainer(options); }
                if (!clearToast($toastElement, options, clearOptions)) {
                    clearContainer(options);
                }
            }

            function remove($toastElement) {
                var options = getOptions();
                if (!$container) { getContainer(options); }
                if ($toastElement && $(':focus', $toastElement).length === 0) {
                    removeToast($toastElement);
                    return;
                }
                if ($container.children().length) {
                    $container.remove();
                }
            }

            // internal functions

            function clearContainer (options) {
                var toastsToClear = $container.children();
                for (var i = toastsToClear.length - 1; i >= 0; i--) {
                    clearToast($(toastsToClear[i]), options);
                }
            }

            function clearToast ($toastElement, options, clearOptions) {
                var force = clearOptions && clearOptions.force ? clearOptions.force : false;
                if ($toastElement && (force || $(':focus', $toastElement).length === 0)) {
                    $toastElement[options.hideMethod]({
                        duration: options.hideDuration,
                        easing: options.hideEasing,
                        complete: function () { removeToast($toastElement); }
                    });
                    return true;
                }
                return false;
            }

            function createContainer(options) {
                $container = $('<div/>')
                    .attr('id', options.containerId)
                    .addClass(options.positionClass);

                $container.appendTo($(options.target));
                return $container;
            }

            function getDefaults() {
                return {
                    tapToDismiss: true,
                    toastClass: 'toast',
                    containerId: 'toast-container',
                    debug: false,

                    showMethod: 'fadeIn', //fadeIn, slideDown, and show are built into jQuery
                    showDuration: 300,
                    showEasing: 'swing', //swing and linear are built into jQuery
                    onShown: undefined,
                    hideMethod: 'fadeOut',
                    hideDuration: 1000,
                    hideEasing: 'swing',
                    onHidden: undefined,
                    closeMethod: false,
                    closeDuration: false,
                    closeEasing: false,
                    closeOnHover: true,

                    extendedTimeOut: 1000,
                    iconClasses: {
                        error: 'toast-error',
                        info: 'toast-info',
                        success: 'toast-success',
                        warning: 'toast-warning'
                    },
                    iconClass: 'toast-info',
                    positionClass: 'toast-top-right',
                    timeOut: 5000, // Set timeOut and extendedTimeOut to 0 to make it sticky
                    titleClass: 'toast-title',
                    messageClass: 'toast-message',
                    escapeHtml: false,
                    target: 'body',
                    closeHtml: '<button type="button">&times;</button>',
                    closeClass: 'toast-close-button',
                    newestOnTop: true,
                    preventDuplicates: false,
                    progressBar: false,
                    progressClass: 'toast-progress',
                    rtl: false
                };
            }

            function publish(args) {
                if (!listener) { return; }
                listener(args);
            }

            function notify(map) {
                var options = getOptions();
                var iconClass = map.iconClass || options.iconClass;

                if (typeof (map.optionsOverride) !== 'undefined') {
                    options = $.extend(options, map.optionsOverride);
                    iconClass = map.optionsOverride.iconClass || iconClass;
                }

                if (shouldExit(options, map)) { return; }

                toastId++;

                $container = getContainer(options, true);

                var intervalId = null;
                var $toastElement = $('<div/>');
                var $titleElement = $('<div/>');
                var $messageElement = $('<div/>');
                var $progressElement = $('<div/>');
                var $closeElement = $(options.closeHtml);
                var progressBar = {
                    intervalId: null,
                    hideEta: null,
                    maxHideTime: null
                };
                var response = {
                    toastId: toastId,
                    state: 'visible',
                    startTime: new Date(),
                    options: options,
                    map: map
                };

                personalizeToast();

                displayToast();

                handleEvents();

                publish(response);

                if (options.debug && console) {
                    console.log(response);
                }

                return $toastElement;

                function escapeHtml(source) {
                    if (source == null) {
                        source = '';
                    }

                    return source
                        .replace(/&/g, '&amp;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');
                }

                function personalizeToast() {
                    setIcon();
                    setTitle();
                    setMessage();
                    setCloseButton();
                    setProgressBar();
                    setRTL();
                    setSequence();
                    setAria();
                }

                function setAria() {
                    var ariaValue = '';
                    switch (map.iconClass) {
                        case 'toast-success':
                        case 'toast-info':
                            ariaValue =  'polite';
                            break;
                        default:
                            ariaValue = 'assertive';
                    }
                    $toastElement.attr('aria-live', ariaValue);
                }

                function handleEvents() {
                    if (options.closeOnHover) {
                        $toastElement.hover(stickAround, delayedHideToast);
                    }

                    if (!options.onclick && options.tapToDismiss) {
                        $toastElement.click(hideToast);
                    }

                    if (options.closeButton && $closeElement) {
                        $closeElement.click(function (event) {
                            if (event.stopPropagation) {
                                event.stopPropagation();
                            } else if (event.cancelBubble !== undefined && event.cancelBubble !== true) {
                                event.cancelBubble = true;
                            }

                            if (options.onCloseClick) {
                                options.onCloseClick(event);
                            }

                            hideToast(true);
                        });
                    }

                    if (options.onclick) {
                        $toastElement.click(function (event) {
                            options.onclick(event);
                            hideToast();
                        });
                    }
                }

                function displayToast() {
                    $toastElement.hide();

                    $toastElement[options.showMethod](
                        {duration: options.showDuration, easing: options.showEasing, complete: options.onShown}
                    );

                    if (options.timeOut > 0) {
                        intervalId = setTimeout(hideToast, options.timeOut);
                        progressBar.maxHideTime = parseFloat(options.timeOut);
                        progressBar.hideEta = new Date().getTime() + progressBar.maxHideTime;
                        if (options.progressBar) {
                            progressBar.intervalId = setInterval(updateProgress, 10);
                        }
                    }
                }

                function setIcon() {
                    if (map.iconClass) {
                        $toastElement.addClass(options.toastClass).addClass(iconClass);
                    }
                }

                function setSequence() {
                    if (options.newestOnTop) {
                        $container.prepend($toastElement);
                    } else {
                        $container.append($toastElement);
                    }
                }

                function setTitle() {
                    if (map.title) {
                        var suffix = map.title;
                        if (options.escapeHtml) {
                            suffix = escapeHtml(map.title);
                        }
                        $titleElement.append(suffix).addClass(options.titleClass);
                        $toastElement.append($titleElement);
                    }
                }

                function setMessage() {
                    if (map.message) {
                        var suffix = map.message;
                        if (options.escapeHtml) {
                            suffix = escapeHtml(map.message);
                        }
                        $messageElement.append(suffix).addClass(options.messageClass);
                        $toastElement.append($messageElement);
                    }
                }

                function setCloseButton() {
                    if (options.closeButton) {
                        $closeElement.addClass(options.closeClass).attr('role', 'button');
                        $toastElement.prepend($closeElement);
                    }
                }

                function setProgressBar() {
                    if (options.progressBar) {
                        $progressElement.addClass(options.progressClass);
                        $toastElement.prepend($progressElement);
                    }
                }

                function setRTL() {
                    if (options.rtl) {
                        $toastElement.addClass('rtl');
                    }
                }

                function shouldExit(options, map) {
                    if (options.preventDuplicates) {
                        if (map.message === previousToast) {
                            return true;
                        } else {
                            previousToast = map.message;
                        }
                    }
                    return false;
                }

                function hideToast(override) {
                    var method = override && options.closeMethod !== false ? options.closeMethod : options.hideMethod;
                    var duration = override && options.closeDuration !== false ?
                        options.closeDuration : options.hideDuration;
                    var easing = override && options.closeEasing !== false ? options.closeEasing : options.hideEasing;
                    if ($(':focus', $toastElement).length && !override) {
                        return;
                    }
                    clearTimeout(progressBar.intervalId);
                    return $toastElement[method]({
                        duration: duration,
                        easing: easing,
                        complete: function () {
                            removeToast($toastElement);
                            clearTimeout(intervalId);
                            if (options.onHidden && response.state !== 'hidden') {
                                options.onHidden();
                            }
                            response.state = 'hidden';
                            response.endTime = new Date();
                            publish(response);
                        }
                    });
                }

                function delayedHideToast() {
                    if (options.timeOut > 0 || options.extendedTimeOut > 0) {
                        intervalId = setTimeout(hideToast, options.extendedTimeOut);
                        progressBar.maxHideTime = parseFloat(options.extendedTimeOut);
                        progressBar.hideEta = new Date().getTime() + progressBar.maxHideTime;
                    }
                }

                function stickAround() {
                    clearTimeout(intervalId);
                    progressBar.hideEta = 0;
                    $toastElement.stop(true, true)[options.showMethod](
                        {duration: options.showDuration, easing: options.showEasing}
                    );
                }

                function updateProgress() {
                    var percentage = ((progressBar.hideEta - (new Date().getTime())) / progressBar.maxHideTime) * 100;
                    $progressElement.width(percentage + '%');
                }
            }

            function getOptions() {
                return $.extend({}, getDefaults(), toastr.options);
            }

            function removeToast($toastElement) {
                if (!$container) { $container = getContainer(); }
                if ($toastElement.is(':visible')) {
                    return;
                }
                $toastElement.remove();
                $toastElement = null;
                if ($container.children().length === 0) {
                    $container.remove();
                    previousToast = undefined;
                }
            }

        })();
    });
}(typeof define === 'function' && define.amd ? define : function (deps, factory) {
    if (typeof module !== 'undefined' && module.exports) { //Node
        module.exports = factory(require('jquery'));
    } else {
        window.toastr = factory(window.jQuery);
    }
}));
;var app = {
    inProgress: false,

    setProgress: function(on){
        app.inProgress = on;
        if(!on) {
            $('.btn-progress').each(function() {
                var $this = $(this);
                $this.html($this.attr('data-orig-caption')).removeAttr('disabled').removeAttr('data-orig-caption');
            });
            $('#ajax-modal .modal-footer .btn').removeAttr('disabled').removeClass('disabled');
            $('.card-progress').hide();
        }else{
            $('.btn-progress').each(function() {
                var $this = $(this);
                if(!$this.attr('data-orig-caption')) {
                    $this.attr('data-orig-caption', $this.html());
                }

                $this.html('<i class="fas fa-circle-notch fa-spin"></i>');
            });

            $('.card-progress').removeClass('d-none').show();
            $('#ajax-modal .modal-footer .btn').attr('disabled', 'disabled').addClass('disabled');
        }
    },

    reloadTimeline: function (fid){
        var data = {};

        $.ajax({
            url: '/ajax/timeline/reload/?fid=' + fid,
            data: data,
            success : function(data) {
                processJSONResponse(data);
            }
        });
    },

    clearConnectedSelect: function(selector){
        $(selector).prop('disabled', true).find('optgroup, option').remove().html('');

        if ($(selector).data('connected-select') !== undefined) {
            var subclear = $( $(selector).data('connected-select') );
            app.clearConnectedSelect( subclear );
        }
    },

    updateConnectedSelects: function(selector, value){
        $( selector ).each(function(idx, select) {
            var $subselect = $(select);

            app.clearConnectedSelect($subselect);

            if (value !== '0') {
                var data = {};
                if($subselect.data('params')) {
                    data = $subselect.data('params');
                }
                data.id = value;

                var fields = $subselect.data('fields');
                if (fields !== undefined) {
                    $(fields).each(function(){
                        data[$(this).prop('name')] = $(this).val();
                    });
                }

                $.ajax({
                    url: '/ajax/lists/' + $subselect.data('list') + '/',
                    data: data,
                    success : function(data) {
                        if(typeof data !== 'object'){
                            data = JSON.parse(data);
                        }

                        app.fillConnectedSelect($subselect, data);
                    }
                });
            }
        });
    },

    fillConnectedSelect: function(select, data){
        var $select = $(select);
        var defaultValue = $select.data('default-value') || 0;
        var defaultSelectFirst = $select.data('default-select-first') || false;
        var group = false;
        var found = false;
        var selected = false;
        var html = '';
        var optionData = '';

        if ($select.data('empty-option')) {
            html += '<option value="0">' + $select.data('empty-option') + '</option>';
        }

        if(data) {
            $.each(data, function (idx, val) {
                selected = false;

                if (val.groupId !== group && val.groupId) {

                    if (group) {
                        html += '</optgroup>';
                        group = false;
                    }

                    html += '<optgroup label="' + val.groupName + '">';
                    group = val.groupId;
                }

                if(val.id) {
                    if(Array.isArray(defaultValue)){
                        if(defaultValue.indexOf(val.id) !== -1){
                            found = true;
                            selected = true;
                        }
                    }else {
                        if (val.id == defaultValue) {
                            found = true;
                            selected = true;
                        }
                    }

                    if(val.data){
                        optionData = '';
                        $.each(val.data, function (idx, val) {
                            optionData += ' data-' + idx + '="' + val + '"';
                        });
                    }

                    html += '<option value="' + val.id + '"' + (selected ? ' selected="selected"' : '') +  optionData + (val.style ? ' style="' + val.style + '"' : '') + (val.class ? ' class="' + val.class + '"' : '') + '>' + val.text + '</option>';
                }
            });
        }

        if (group) {
            html += '</optgroup>';
        }

        $select.append(html);
        $select.removeAttr('disabled');

        if(!found && defaultSelectFirst) {
            $select.find('option').first().prop('selected', true);
        }

        if($select.hasClass('select-picker')){
            $select.selectpicker('refresh');
        }

        $select.trigger('change');
    },

    initControls: function(){
        $(document).on('click', '.checkbox-highlight input', function () {
            var $this = $(this);
            var $container = $this.parents('.checkbox-highlight');
            var active = $container.data('active') || '';
            var inactive = $container.data('inactive') || '';

            if($this.is(':checked')){
                if(inactive != '') $container.removeClass(inactive);
                if(active != '') $container.addClass(active);
            }else{
                if(inactive != '') $container.addClass(inactive);
                if(active != '') $container.removeClass(active);
            }
        });

        /*
        $(document).on('click', '.btn-progress', function () {
            var $this = $(this);

            if(!$this.attr('data-orig-caption')) {
                $this.attr('data-orig-caption', $this.html());
            }

            $this.html('<i class="fas fa-circle-notch fa-spin"></i>');
        });
        */

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            $('input[name=' + $(e.target).parent().attr('id') + ']').val( $(e.target).attr('href').replace('#', '') );
        });

        $(document).on('click', '.btn-modal-submit', function(){
            var $this = $(this);
            var $modal = $this.parents('.modal-content');
            var $form = $modal.find('form');
            $form.submit();
        });

        $(document).on('keyup', '.alphanumeric-only', function(){
            this.value = this.value.replace(/[^a-z0-9]/ig, "");
        });

        $(document).on('blur', '.alphanumeric-only', function(){
            this.value = this.value.replace(/[^a-z0-9]/ig, "");
        });

        $(document).on('blur', '.time-format', function () {
            var $this = $(this);
            var time = $this.val();
            $this.val(moment(time.toString(), 'LT').format('HH:mm'));
        });

        $(document).on('keyup', '.numbersonly', function(){
            var chars = $(this).data('chars');
            var replaceCharFrom = $(this).data('replace-from');
            var replaceCharTo = $(this).data('replace-to');

            if(replaceCharFrom && replaceCharTo){
                this.value = this.value.replace(replaceCharFrom, replaceCharTo);
            }

            if(chars === '' || typeof chars === 'undefined'){
                chars = '\\-.,';
            }
            var pattern = '[^0-9' + chars + ']';
            var re = new RegExp(pattern, 'ig');
            this.value = this.value.replace(re, "");
        });

        $(document).on('blur', '.numbersonly', function(){
            var chars = $(this).data('chars');
            if(chars === '' || typeof chars === 'undefined'){
                chars = '\\-.,';
            }

            var pattern = '[^0-9' + chars + ']';
            var re = new RegExp(pattern, 'ig');
            this.value = this.value.replace(re, "");
        });

        $('.btn-insert-text').on('click', function () {
            var $this = $(this);
            var $editor = $this.parents('fieldset').find('.htmleditor');

            $editor.summernote('editor.saveRange');
            $editor.summernote('editor.restoreRange');
            $editor.summernote('editor.focus');
            $editor.summernote('editor.insertText', $this.html());
        });

        $(document).on('change', '.change-currency-sign', function () {
            var $this = $(this);
            if($this.val() != '0') {
                var text = $this.find('option:selected').text();
                $('.has-currency-sign').parents('.input-group').find('.input-group-text').html(text);
            }
        });

        $(document).on('change', '.change-state-on-change', function () {
            var $this = $(this);
            var enabledFields = $this.data('enable-fields');
            var disableFields = $this.data('disable-fields');
            var enableValue = $this.data('enable-value');
            var disableValue = $this.data('disable-value');

            var readonlyFields = $this.data('readonly-fields');
            var readonlyValue = $this.data('readonly-value');

            var visibleIds = $this.data('visible-ids');
            var visibleValue = $this.data('visible-value');

            if(enabledFields) {
                $(enabledFields).each(function (idx, obj) {
                    if ($this.val() == enableValue) {
                        $(obj).removeAttr('disabled');
                    } else {
                        $(obj).attr('disabled', 'disabled');
                    }
                });
            }

            if(disableFields) {
                $(disableFields).each(function (idx, obj) {
                    if($this.val() == disableValue){
                        $(obj).attr('disabled', 'disabled');
                    }else{
                        $(obj).removeAttr('disabled');
                    }
                });
            }

            if(readonlyFields) {
                $(readonlyFields).each(function (idx, obj) {
                    if($this.val() == readonlyValue){
                        $(obj).removeAttr('readonly');
                    }else{
                        $(obj).attr('readonly', 'readonly');
                    }
                });
            }

            if(visibleIds) {
                $(visibleIds).each(function (idx, obj) {
                    if($this.val() == visibleValue){
                        $(obj).removeClass('d-none').show();
                    }else{
                        $(obj).hide();
                    }
                });
            }
        });

        $(document).on('change', '.change-state', function () {
            var $this = $(this);
            var options = $this.data('stateOptions');
            var value, found = false;

            if(options) {
                if (this.type && (this.type === 'checkbox' || this.type === 'radio')) {
                    value = ($this.is(':checked') ? 1 : 0);
                } else {
                    value = $this.val();
                }

                /*
                if(typeof options !== 'object'){
                    options = JSON.parse(options);
                }
                */

                $.each(options, function (val, opt) {
                    if (val == value) {
                        found = true;
                        $.each(opt, function (action, elements) {
                            if (action === 'show') {
                                $(elements).removeClass('d-none').show();
                            } else if (action === 'hide') {
                                $(elements).hide();
                            } else if (action === 'disable') {
                                $(elements).attr('disabled', 'disabled');
                            } else if (action === 'enable') {
                                $(elements).removeAttr('disabled');
                            } else if (action === 'readonly') {
                                $(elements).attr('readonly', 'readonly');
                            } else if (action === 'editable') {
                                $(elements).removeAttr('readonly');
                            } else if (action === 'value') {
                                $(elements.el).val(elements.val).trigger('change');
                            }
                        });
                    }
                });

                if (!found) {
                    var def = $this.data('stateDefault');
                    if (def) {
                        $.each(def, function (action, elements) {
                            if (action === 'show') {
                                $(elements).removeClass('d-none').show();
                            } else if (action === 'hide') {
                                $(elements).hide();
                            } else if (action === 'disable') {
                                $(elements).attr('disabled', 'disabled');
                            } else if (action === 'enable') {
                                $(elements).removeAttr('disabled');
                            } else if (action === 'readonly') {
                                $(elements).attr('readonly', 'readonly');
                            } else if (action === 'editable') {
                                $(elements).removeAttr('readonly');
                            } else if (action === 'value') {
                                $(elements.el).val(elements.val);
                            }
                        });
                    }
                }
            }
        });

        $('[data-toggle="tooltip"]').tooltip();

        $("table.user_level_rights input:checkbox").on("click",
            function(e){
                var $this = $(this);
                var _userGroup = $this.attr('data-usergroup');
                var _role = $this.attr('data-role');
                var _page = $this.attr('data-page');
                var _function = $this.attr('data-function');
                if(!_function){
                    _function = '';
                }

                $.ajax({
                    url: "/ajax/accessrights/",
                    data: "usergroup=" + _userGroup + "&role=" + _role + "&page=" + _page + "&function=" + _function + "&checked=" + (($this.is(":checked")) ? 1 : 0)
                });
            }
        );

        $("table.user_level_rights .btn-accesslevel").on("click",
            function(e){
                var $this = $(this);
                var $button = $this.closest('.btn-group').find('button');
                var _value = $this.attr('data-value');
                var _color = $this.attr('data-color');
                var _icon = $this.attr('data-ico');
                var _userGroup = $this.closest('.btn-group').attr('data-usergroup');
                var _role = $this.closest('.btn-group').attr('data-role');
                var _page = $this.closest('.btn-group').attr('data-page');
                var _current_icon = $button.attr('data-ico');
                var _current_color = $button.attr('data-color');

                $.ajax({
                    url: "/ajax/accessrights/",
                    data: "usergroup=" + _userGroup + "&role=" + _role + "&page=" + _page + "&value=" + _value,
                    success: function(data){
                        $button.toggleClass('btn-' + _color + ' btn-' + _current_color).attr('data-color', _color).attr('data-ico', _icon);
                        $button.find('i').toggleClass('fa-' + _icon + ' fa-' + _current_icon );
                    }
                });
            }
        );

        /*
        if ($.fn.parsley) {
            $('.parsley-form:not(.inited)').each (function () {
                $(this).addClass('inited');
                $(this).parsley ({
                    trigger: 'change',
                    errorClass: '',
                    successClass: '',
                    errorsWrapper: '<div></div>',
                    errorTemplate: '<label class="error"></label>',
                }).on('field:success', function (ParsleyField) {
                    var $container = ParsleyField.$element.parents('.form-group');
                    $container.removeClass('has-error');
                }).on('field:error', function (ParsleyField) {
                    var $container = ParsleyField.$element.parents('.form-group');
                    $container.removeClass('has-success').addClass('has-error');

                    $('.btn-progress').html($('.btn-progress').data('orig-caption'));
                    $('.btn-progress').parents('form').find('.card-progressbar').addClass('d-none');
                });
            });
        }
         */

        $('[data-toggle="accept"]').on('click', function (){
            var $this = $(this);
            if($this.is(':checked')){
                $this.parents('form').find('.btn-accept').removeAttr('disabled');
            }else{
                $this.parents('form').find('.btn-accept').attr('disabled', 'disabled');
            }
        });

        $(".upload-profile-img").on('click', function(e){
            e.preventDefault();
            $("#fileInput:hidden").trigger('click');
        });

        $(".delete-profile-img").on('click', function(e){
            e.preventDefault();
            $.ajax({
                url: "/ajax/fileupload/delete-profile-img/",
                success: function(data) {
                    processJSONResponse(data);
                }
            });
        });

        $('#fileInput').on('change', function(){
            var image = $(this).val();
            var img_ex = /(\.jpg|\.jpeg|\.png|\.gif)$/i;

            if(!img_ex.exec(image)){
                app.showMessage({type: 'warning', message: 'Hibás fájltípus! Engedélyezett fájltípusok: jpg, png, gif'});

                $('#fileInput').val('');
                return false;
            }else{
                $('#img-upload-form').submit();
            }
        });

        $('#img-upload-form').on('submit',(function(e) {
            var $this = $(this);
            e.preventDefault();
            $.ajax({
                url: $this.attr('action'),
                type: "POST",
                data:  new FormData(this),
                contentType: false,
                cache: false,
                processData:false,
                success: function(data){
                    processJSONResponse(data);
                },
                error: function(e){
                    app.showMessage({type: 'error', message: 'Invalid file.'});
                }
            });
        }));

        $(".chk-notification").on("click",
            function(e){
                var $this = $(this);
                var id = parseInt($this.data('id'));
                var type = $this.data('type');
                var uid = parseInt($this.data('uid'));
                var value = ($this.is(':checked') ? 1 : 0);

                $.ajax({
                    url: "/ajax/subscribe-notification/",
                    data: "id=" + id + "&type=" + type + "&uid=" + uid + "&value=" + value,
                    success: function(data){
                    }
                });
            }
        );

        if(jQuery().select2) {
            $('.select2:not(.inited)').addClass('inited').select2();
        }

        if(jQuery().selectpicker) {
            $('.select-picker:not(.inited)').addClass('inited').selectpicker();
        }

        if($('.htmleditor').length > 0){
            $('.htmleditor').summernote({
                height: 300,
                toolbar: [
                    ['edit', ['undo', 'redo']],
                    //['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'fontsize', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['table', 'link', 'gallery', 'video']], // 'picture'
                    ['misc', ['fullscreen', 'codeview']],
                    ['cleaner',['cleaner']]
                ],
                cleaner:{
                    action: 'both',
                    newline: '<br>',
                    notStyle: 'position:hidden;top:0;left:0;right:0',
                    icon: '<i class=\"fa fa-eraser\"></i>',
                    keepHtml: false,
                    keepOnlyTags: ['<p>', '<br>', '<ul>', '<li>', '<b>', '<strong>','<i>', '<a>'],
                    keepClasses: false,
                    badTags: ['style', 'script', 'applet', 'embed', 'noframes', 'noscript', 'html'],
                    badAttributes: ['style', 'start', 'class'],
                    limitChars: false,
                    limitDisplay: 'none',
                    limitStop: false
                },
                callbacks :{
                    onInit: function() {
                        $(this).data('image_dialog_images_url', "/ajax/gallery-content/");
                        $(this).data('image_dialog_title', "");
                    }
                }
            });
        }

        $('.btn-notification').on('click', function (){
            var $this = $(this);
            var url = $this.data('url');
            var id = parseInt($this.data('id'));

            $this.removeClass('note-new');
            $this.parent().addClass('viewed');

            $.ajax({
                url: "/ajax/notifications/viewed/",
                data: "id=" + id,
                success: function(data){
                    processJSONResponse(data);
                    if(url){
                        window.location = url;
                    }
                }
            });
        });

        initInfiniteScroll();

        this.reInit();
    },

    reInit: function(){
        if(jQuery().select2) {
            $('.select2:not(.inited)').addClass('inited').select2();
        }

        if(jQuery().selectpicker) {
            $('.select-picker:not(.inited)').addClass('inited').selectpicker();
        }

        if ($.fn.parsley) {
            $('.parsley-form:not(.inited)').each (function () {
                $(this).addClass('inited');
                $(this).parsley ({
                    trigger: 'change',
                    errorClass: '',
                    successClass: '',
                    errorsWrapper: '<div></div>',
                    errorTemplate: '<label class="error"></label>',
                }).on('field:success', function (ParsleyField) {
                    var $container = ParsleyField.$element.parents('.form-group');
                    $container.removeClass('has-error');
                }).on('field:error', function (ParsleyField) {
                    var $container = ParsleyField.$element.parents('.form-group');
                    $container.removeClass('has-success').addClass('has-error');

                    app.setProgress(false);
                });
            });
        }

        $('input.tagsinput:not(.inited)').each(function(){
            $(this).addClass('inited');
            $(this).tagsinput({
                trimValue: true,
                freeText: true
            });
            $(this).tagsinput('input').attr('data-parsley-ui-enabled', 'false').on('blur', function(){
                if ($(this).val()) {
                    $(this).parent().parent().find('.tagsinput').tagsinput('add', $(this).val());
                    $(this).val('');
                }
            });
        });

        $('select.tagsinput:not(.inited)').each(function(){
            $(this).addClass('inited');
            $(this).tagsinput({
                itemValue: 'id',
                itemText: 'label'
            });
            $(this).find('option').each(function(){
                $(this).parent().tagsinput('add', { "id": $(this).attr('value'), "label": $(this).html() });
            });

            $(this).tagsinput('input').parent().addClass('bootstrap-tagsinput-fullwidth');
            $(this).tagsinput('input')
                .attr('data-list-url', $(this).attr('data-list-url'))
                .attr('data-scope', $(this).attr('data-scope'))
                .addClass('catselautocomplete');
        });

        $('.autocomplete:not(.inited)').each(function() {
            var $this = $(this);
            $this.addClass('inited');

            var _data = {};
            var _changed = false;
            var id = $this.attr('id');
            var addNew = parseInt($this.data('addnew')) || 0;
            var insertFields = $this.data('insertfields') || false;
            var searchFields = $this.data('searchfields') || false;
            var callBackFunction = $this.data('callback') || false;
            var clearOnSelect = $this.data('clearonselect') || false;
            var list = $this.data('list') || '';
            var url = '/ajax/lists/' + list + '/';

            $this.autoComplete({
                resolver: 'custom',
                minLength: 0,
                preventEnter: true,
                events: {
                    search: function (q, callback) {
                        _data = {};
                        _data.q = q;

                        if(searchFields){
                            $(searchFields).each(function(idx, inp){
                                _data[$(inp).attr('id')] = $(inp).val();
                            });
                        }

                        $.ajax(
                            url,
                            {
                                data: _data
                            }
                        ).done(function (res) {
                            if(res.results) {
                                callback(res.results);
                            }
                        });
                    }
                }
            });

            $this.on('focus', function () {
                $this.autoComplete('show');
            });

            $this.on('change', function () {
                _changed = true;
            });

            $this.on('keyup', function (e) {
                if(e.keyCode === 13) {
                    e.preventDefault();
                    _changed = true;
                    $this.trigger('autocomplete.freevalue', [ $this.val() ]);
                }
            });

            $this.on('autocomplete.select', function (evt, item) {
                $('#' + id + '-id').val(item.id);
                _changed = false;

                _data = {};
                _data.id = item.id;
                _data.action = 'count';

                $.ajax(
                    url,
                    {
                        data: _data
                    }
                );

                if(callBackFunction){
                    var fn = window[callBackFunction];
                    if(typeof fn === 'function'){
                        console.log('call function');
                        _data = {};
                        _data.id = item.id;

                        if(insertFields){
                            $(insertFields).each(function(idx, inp){
                                _data[$(inp).attr('id')] = $(inp).val();
                            });
                        }

                        fn.call(null, item);
                    }
                }

                if(clearOnSelect){
                    $this.autoComplete('clear');
                    $('#' + id + '-id').val(0);
                }
            });

            $this.on('autocomplete.freevalue', function (evt, value) {
                if(addNew){
                    if(_changed && value !== '') {
                        _changed = false;
                        _data = {};
                        _data.value = value;
                        _data.action = 'add';
                        if(insertFields){
                            $(insertFields).each(function(idx, inp){
                                _data[$(inp).attr('id')] = $(inp).val();
                            });
                        }

                        $.ajax(
                            url,
                            {
                                data: _data
                            }
                        ).done(function (res) {
                            if(res.id){
                                $('#' + id + '-id').val(res.id);

                                if(callBackFunction){
                                    var fn = window[callBackFunction];
                                    if(typeof fn === 'function'){
                                        _data = {};
                                        _data.id = res.id;

                                        if(insertFields){
                                            $(insertFields).each(function(idx, inp){
                                                _data[$(inp).attr('id')] = $(inp).val();
                                            });
                                        }

                                        fn.call(null, _data);
                                    }
                                }
                            }
                        });
                    }
                }else {
                    $('#' + id + '-id').val(0);
                }

                if(clearOnSelect){
                    $this.autoComplete('clear');
                    $('#' + id + '-id').val(0);
                }
            });
        });

        $('.catselautocomplete:not(.inited)').each(function() {
            var $this  = $(this);
            var _url  = $this.attr('data-list-url');
            var _scope = $this.attr('data-scope') || 0;
            $this.addClass('inited');

            $this.catselcomplete({
                minLength: 2,
                source: function(request, response) {
                    $.ajax({
                        url: _url,
                        dataType: "jsonp",
                        data: {
                            query: request.term,
                            scope: _scope,
                        },
                        success : function(data) {
                            var resp = {};
                            for (var i in data) {
                                var val = data[i];
                                if (val.categoryid !== undefined && resp[val.categoryid] === undefined) {
                                    resp[val.categoryid] = {
                                        value: val.categoryid,
                                        label: val.category,
                                        category: val.category,
                                        categorycode: val.categoryid,
                                        class: 'ui-autocomplete-category'
                                    };
                                }
                                if (val.id !== val.categoryid) {
                                    resp[val.id] = {
                                        value: val.id,
                                        label: val.label,
                                        category: val.category,
                                        categorycode: val.categoryid,
                                        class: 'ui-menu-item'
                                    };
                                }
                            }
                            response(resp);
                        }
                    });
                },
                focus: function( event, ui ) {
                    if ($(this).parent().hasClass('bootstrap-tagsinput')) {
                    } else {
                        $( this ).val( ui.item.label );
                        $('#' + this.id + '-id').val( ui.item.value );
                    }
                    return false;
                },
                select: function(event, ui) {
                    if ($(this).parent().hasClass('bootstrap-tagsinput')) {
                        $(this).parent().next().tagsinput('add', { "id": ui.item.value, "label": ui.item.label });
                        $(this).val('');
                    } else {
                        $(this).val( ui.item.label );
                        $('#' + this.id + '-id').val( ui.item.value );
                    }
                    return false;
                }
            }).focusout(function() {
                if ($(this).parent().hasClass('bootstrap-tagsinput')) {
                } else {
                    if ($(this).val() == '') {
                        $('#' + this.id + '-id').val('');
                    }
                }
            });
        });

        if ($.fn.datepicker) {
            $('.datepicker:not(.inited)').each(function() {

                var $this = $(this);
                $this.addClass('inited');
                var _language = $this.attr('data-language') || 'en';
                var _firstday = parseInt($this.attr('data-firstday') || 1);
                var _dateformat = $this.attr('data-dateformat') || 'yy-mm-dd';
                var _calendars = parseInt($this.attr('data-calendars') || 1);
                var _changeyear = $this.attr('data-change-year') || 'false';
                var _changemonth = $this.attr('data-change-month') || 'true';
                _changeyear = (_changeyear === 'true' ? true : false );
                _changemonth = (_changemonth === 'true' ? true : false );

                if(_changeyear){
                    var _yearrange = $this.attr('data-year-range') || 'c-10:c+10';
                }else{
                    var _yearrange = 'c-10:c+10';
                }

                var _mindate = $this.attr('data-min-date') || '';
                var _maxdate = $this.attr('data-max-date') || '';

                var _open = $this.attr('data-open') || false;
                var _datelimitmin = $this.attr('data-datelimit-min') || false;
                var _datelimitmax = $this.attr('data-datelimit-max') || false;

                var _rangefrom = $this.attr('data-range-from') || false;
                var _rengeto = $this.attr('data-range-to') || false;

                $this.datepicker($.extend({}, $.datepicker.regional[_language],{
                    firstDay: _firstday,
                    dateFormat: _dateformat,
                    numberOfMonths: _calendars,
                    changeMonth: _changemonth,
                    changeYear: _changeyear,
                    yearRange: _yearrange,
                    minDate: _mindate,
                    maxDate: _maxdate,
                    prevText: '',
                    nextText: '',
                    beforeShowDay: function( date ) {
                        if (_rengeto && $('#' + _rengeto)) {
                            var date1 = $.datepicker.parseDate(_dateformat, $(this).val());
                            var date2 = $.datepicker.parseDate(_dateformat, $('#' + _rengeto).val());
                        } else if (_rangefrom && $('#' + _rangefrom)) {
                            var date1 = $.datepicker.parseDate(_dateformat, $('#' + _rangefrom).val());
                            var date2 = $.datepicker.parseDate(_dateformat, $(this).val());
                        }
                        var extra_class = '';
                        if (date1 && date.getTime() === date1.getTime()) {
                            extra_class += ' dp-range-start';
                        }
                        if (date2 && date.getTime() === date2.getTime()) {
                            extra_class += ' dp-range-end';
                        }
                        return [true, date1 && date2 && (date >= date1 && date <= date2) ? "dp-highlight" + extra_class : extra_class];
                    },
                    onClose: function( selectedDate ) {
                        if(_datelimitmin && $('#' + _datelimitmin)) $('#' + _datelimitmin).datepicker( "option", "minDate", selectedDate );
                        if(_datelimitmax && $('#' + _datelimitmax)) $('#' + _datelimitmax).datepicker( "option", "maxDate", selectedDate );
                        if(_open && $('#' + _open) && selectedDate!='') $('#' + _open).datepicker('show');
                    },
                    onSelect: function(dateText, inst) {
                        $(this).trigger('onblur');
                        $(this).trigger('change');
                    }
                }));
            });

            if (!$('body').hasClass('datepicker-inited')) {
                $('body').addClass('datepicker-inited');
                $('body').on(
                    'mouseenter',
                    '#ui-datepicker-div .ui-datepicker-calendar td:not(.ui-datepicker-unselectable,.ui-state-disabled)',
                    function() {
                        if ($('.ui-datepicker-calendar .dp-range-start, .ui-datepicker-calendar .dp-range-end').length > 0) {
                            $(this).addClass('dp-state-hover');
                            var rangefrom = false;
                            var rangeto = false;
                            if ($('.ui-datepicker-calendar .dp-range-start.dp-range-end .ui-state-active').length > 0) {
                                rangeto = true;
                            } else if ($('.ui-datepicker-calendar .dp-range-start .ui-state-active').length > 0) {
                                rangefrom = true;
                            } else if ($('.ui-datepicker-calendar .dp-range-end .ui-state-active').length > 0) {
                                rangeto = true;
                            } else if (!rangefrom && $('.ui-datepicker-calendar .dp-range-start').length > 0) {
                                rangeto = true;
                            } else if (!rangeto && $('.ui-datepicker-calendar .dp-range-end').length > 0) {
                                rangefrom = true;
                            }

                            $('.ui-datepicker-calendar .dp-range-start').addClass('dp-range-start-h').removeClass('dp-range-start');
                            $('.ui-datepicker-calendar .dp-range-end').addClass('dp-range-end-h').removeClass('dp-range-end');
                            $('.ui-datepicker-calendar .dp-highlight').addClass('dp-highlight-h').removeClass('dp-highlight');
                            $('.ui-datepicker-calendar .ui-state-active').addClass('ui-state-active-h').removeClass('ui-state-active');
                            var hoverfound = false;
                            var startfound = false;
                            var endfound = false;
                            var started = false;
                            var ended = false;
                            $('#ui-datepicker-div .ui-datepicker-calendar td:not(.ui-datepicker-unselectable,.ui-state-disabled)').each(function(idx, td){
                                if ($(td).hasClass('dp-state-hover')) {
                                    hoverfound = true;
                                }
                                if ($(td).hasClass('dp-range-start-h')) {
                                    startfound = true;
                                }
                                if (!startfound && $(td).hasClass('dp-highlight-h')) {
                                    startfound = true;
                                    if (rangeto) {
                                        started = true;
                                    } else {
                                        rangefrom = true;
                                    }
                                }
                                if ($(td).hasClass('dp-range-end-h')) {
                                    endfound = true;
                                }
                                if (!started) {
                                    if (rangefrom && hoverfound && !endfound) {
                                        started = true;
                                        $(td).addClass('dp-hover-start');
                                    }
                                    if (rangeto && startfound && !hoverfound) {
                                        started = true;
                                        $(td).addClass('dp-hover-start');
                                    }
                                } else if (!ended) {
                                    if (rangefrom && !endfound) {
                                        $(td).addClass('dp-hover');
                                    } else if (rangeto && !hoverfound) {
                                        $(td).addClass('dp-hover');
                                    } else {
                                        ended = true;
                                        $(td).addClass('dp-hover-end');
                                    }
                                }
                            });
                        }
                    }
                );
                $('body').on(
                    'mouseleave',
                    '#ui-datepicker-div .ui-datepicker-calendar td:not(.ui-datepicker-unselectable,.ui-state-disabled)',
                    function() {
                        $('#ui-datepicker-div .ui-datepicker-calendar td').removeClass('dp-hover dp-state-hover dp-hover-start dp-hover-end');
                        $('.ui-datepicker-calendar .dp-range-start-h').addClass('dp-range-start').removeClass('dp-range-start-h');
                        $('.ui-datepicker-calendar .dp-range-end-h').addClass('dp-range-end').removeClass('dp-range-end-h');
                        $('.ui-datepicker-calendar .dp-highlight-h').addClass('dp-highlight').removeClass('dp-highlight-h');
                        $('.ui-datepicker-calendar .ui-state-active-h').addClass('ui-state-active').removeClass('ui-state-active-h');
                    }
                );
            }
        }

        $('.connected-select:not(.cs-inited)').each(function(){
            $(this).addClass('cs-inited').on('change', function() {
                var $this = $(this);
                app.updateConnectedSelects($this.data('connected-select'), $this.val());
            });

            if ($(this).val() !== '0' && $(this).val() !== null) {
                $(this).trigger('change');
            }
        });

        //$('.selectpicker').selectpicker();
        $('.change-state-on-change').trigger('change');
        $('.change-state').not('.skip-init').trigger('change');

        var $input = $('input.file[type=file]');
        if ($input.length) {
            $input.fileinput();
        }

        $('.img-zoom').zoom();

        if(typeof autosize == 'function') {
            autosize($('textarea.autosize'));
        }
    },

    initModals: function(){
        $('#ajax-modal').on('show.bs.modal', function (e) {
            var url = '';
            var modal = $(this);
            if(e.relatedTarget) {
                var button = $(e.relatedTarget);
                if (button.data('size')) {
                    $('#ajax-modal .modal-dialog').addClass('modal-' + button.data('size'));
                }

                if (button.attr('href') != '#' && button.attr('href') != '') {
                    url = button.attr('href');
                } else if (button.data('href')) {
                    url = button.data('href');
                }
            }

            if (url != '') {
                modal.find('.modal-content').load(url);
            }
        });

        $('#ajax-modal').on('hidden.bs.modal', function (e) {
            $(e.target).removeData('bs.modal');
            $('#ajax-modal .modal-dialog').removeClass('modal-sm modal-lg modal-xl');
            $('#ajax-modal .modal-content').html('');

            $.ajax({
                url: '/ajax/cleanup/'
            });
        });

        $('#confirm-delete').on('show.bs.modal', function(e) {
            e.stopPropagation();

            var $confirm_button = $(this).find('.danger');
            var color = $(e.relatedTarget).data('color');

            $confirm_button.removeClass('btn-primary btn-secondary btn-info btn-success btn-warning btn-danger');
            $('#confirm-delete').removeClass('modal-outline-warning modal-outline-success modal-outline-primary modal-outline-info modal-outline-danger modal-outline-secondary');
            $('#confirm-delete .modal-header').removeClass('bg-warning bg-success bg-primary bg-info bg-danger bg-secondary');
            $('#confirm-delete .modal-content').removeClass('brc-danger-m2 brc-warning-m2 brc-success-m2 brc-primary-m2 brc-info-m2 brc-secondary-m2');

            if(color){
                $confirm_button.addClass('btn-' + color);
                $('#confirm-delete').addClass('modal-outline-' + color);
                $('#confirm-delete .modal-header').addClass( 'bg-' + color );
                $('#confirm-delete .modal-content').addClass('brc-' + color + '-m2');
            }else{
                $confirm_button.addClass('btn-danger');
                $('#confirm-delete').addClass('modal-outline-danger');
                $('#confirm-delete .modal-header').addClass('bg-danger');
                $('#confirm-delete .modal-content').addClass('brc-danger-m2');
            }

            if ($(e.relatedTarget).data('href') != undefined) {
                $confirm_button.data('href', $(e.relatedTarget).data('href'));
                $confirm_button.on('click',  function(){
                    document.location = $(this).data('href');
                });
            } else if ($(e.relatedTarget).data('confirm-action') != undefined) {
                $confirm_button.attr('onclick', $(e.relatedTarget).data('confirm-action'));
            }
            if($(e.relatedTarget).data('confirm-button') != undefined) {
                $confirm_button.html($(e.relatedTarget).data('confirm-button'));
            } else {
                $confirm_button.html($confirm_button.data('default-caption'));
            }
            if($(e.relatedTarget).data('title') != undefined) {
                $('#confirm-delete .modal-title').html( $(e.relatedTarget).data('title') );
            }else{
                $('#confirm-delete .modal-title').html( $('#confirm-delete .modal-title').data('default-title') );
            }

            $('.confirm-question').html( $(e.relatedTarget).data('confirm-question') );
            $('.debug-data').html(  $(e.relatedTarget).data('confirm-data')  );
        });

        // Keep modal scrollable if a confirm modal opened
        $('#confirm-delete').on('hidden.bs.modal', function () {
            $('.modal-footer .btn:not(.btn-approve), .modal-header button').show();
            $('.modal-footer .modal-loader').remove();

            if($('#ajax-modal').hasClass('in')){
                $('body').addClass('modal-open')
            }

            /*
            $.ajax({
                url: '/ajax/cleanup/'
            });
            */
        });

        $(document).on('focus', '.input-group > input, .input-group > select', function(e){
            $(this).parents('.input-group').addClass("input-group-focus");
        }).on('blur', '.input-group > input, .input-group > select', function(e){
            $(this).parents('.input-group').removeClass("input-group-focus");
        });
    },

    showMessage: function(value){
        toastr.options.progressBar = true;
        toastr.options.closeButton = true;

        switch(value.type){
            case 'warning':
                toastr.warning(value.message);
                break;
            case 'error':
                toastr.error(value.message);
                break;
            case 'success':
                toastr.success(value.message);
                break;
            case 'info':
            default:
                toastr.info(value.message);
                break;
        }
    },

    showMessages: function(){
        if(typeof _messages !== 'undefined'){
            $.each( _messages, function(idx, value) {
                app.showMessage(value);
            });
        }
    },

    initToaster: function(){
        setTimeout(function(){ app.showMessages(); }, 500);
    },

    checkLoginStatus: function(response){

    },

    init: function(){
        this.initControls();
        this.initModals();
        this.initToaster();
    }
};

$(function() {
    $.fn.modal.Constructor.prototype._enforceFocus = function () { };

    $.fn.extend({
        insertAtCaret: function(myValue) {
            this.each(function() {
                if (document.selection) {
                    this.focus();
                    var sel = document.selection.createRange();
                    sel.text = myValue;
                    this.focus();
                } else if (this.selectionStart || this.selectionStart == '0') {
                    var startPos = this.selectionStart;
                    var endPos = this.selectionEnd;
                    var scrollTop = this.scrollTop;
                    this.value = this.value.substring(0, startPos) +
                        myValue + this.value.substring(endPos,this.value.length);
                    this.focus();
                    this.selectionStart = startPos + myValue.length;
                    this.selectionEnd = startPos + myValue.length;
                    this.scrollTop = scrollTop;
                } else {
                    this.value += myValue;
                    this.focus();
                }
            });
            return this;
        }
    });

    // Init Bootstrap Tooltips
    //$('[data-toggle="tooltip"]').tooltip();

    // Init Bootstrap Popovers
    //$('[data-toggle="popover"]').popover();

    app.init();
});

function postModalForm(form, btnValue, btnName) {
    if(!btnValue) btnValue = 1;
    if(!btnName) btnName = 'save';
    var formName = $(form).attr('id').substring(0, ($(form).attr('id').length - 5));
    var data = new FormData($(form).get(0));
    data.append(formName + '[' + btnName + ']', btnValue);
    app.setProgress(true);

    $.ajax({
        type: 'POST',
        url: $(form).attr('action'),
        data: data,
        cache: false,
        processData: false,
        contentType: false,
        success: function(data) {
            $('#confirm-delete').modal('hide');
            if(data) {
                processJSONResponse(data);
                app.setProgress(false);
                app.reInit();
            }
        }
    });
}

function modalFormPageRefresh(formname) {
    $('#ajax-modal').modal('hide');

    var $form = $('form');
    if ($form.length > 0) {
        $form = $form.first();
        $form.append('<input type="hidden" name="modalform" value="' + formname + '" />');
        if ($form.hasClass('parsley-form')) {
            $form.parsley().destroy();
        }
        $form.submit();
    } else {
        var location = window.location.href;
        if (location.indexOf('?') > -1) {
            window.location.href += '&modalform=' + formname;
        } else {
            window.location.href += '?modalform=' + formname;
        }
    }
}

/**
 * @deprecated
 * @param data
 */
function modalFormPageUpdate(data){
    $('#ajax-modal').modal('hide');

    if(data){
        if(typeof data !== 'object'){
            data = JSON.parse(data);
        }
        processJSONResponse(data);
    }
}

function fillValues(data) {
    if(data.fill){
        $.each(data.fill, function(key, value){
            $(key).val(value);
        });
    }
}

function processJSONResponse(data){
    if(typeof data !== 'object'){
        data = JSON.parse(data);
    }

    $.each(data, function(selector, action){
        $.each(action, function(method, value){

            if(typeof window[selector] === 'object') {
                if(typeof window[selector][method] === 'function') {
                    window[selector][method](value);
                }
            }else{
                if(method === 'show'){
                    if(value === true){
                        $(selector).hide().removeClass('d-none').show();
                    }else{
                        $(selector).hide();
                    }
                }else if(method === 'tagsinput'){
                    $.each(value, function(i, avalue) {
                        if(avalue) {
                            $(selector).tagsinput('add', avalue);
                        }
                    });
                }else if(method === 'summernote'){
                    $(selector).summernote('code', value);
                    crm.isdirty = false;
                }else if(method === 'addclass'){
                    $(selector).addClass(value);
                }else if(method === 'removeclass'){
                    $(selector).removeClass(value);
                }else if(method === 'remove'){
                    $(selector).remove();
                }else if(method === 'html'){
                    $(selector).html(value);
                }else if(method === 'closeModal'){
                    $(selector).modal('hide');
                }else if(method === 'attr'){
                    $.each(value, function(attr, avalue) {
                        if(avalue) {
                            $(selector).attr(attr, avalue);
                        }else{
                            $(selector).removeAttr(attr, '');
                        }
                    });
                }else if(method === 'value'){
                    if($(selector).is(':checkbox') || $(selector).is(':radio')) {
                        $(selector).prop('checked', value);
                    }else {
                        $(selector).val(value);
                    }
                }else if(method === 'options') {
                    $(selector).find('option').remove();
                    $(selector).append(value.map(function (val) {
                        return '<option value="' + val.id + '">' + val.name + '</option>'
                    }));
                }else if(method === 'functions'){
                    if(value.callback) {
                        var fn = window[value.callback];
                        if(typeof fn === 'function' ) {
                            fn(value.arguments);
                        }
                    }
                }else{
                    if(typeof window[method] === 'object') {
                        if(typeof window[method][selector] === 'function') {
                            window[method][selector](value);
                        }
                    }
                }
            }
        });
    });

    return data;
}

function initSortable(){
    $('#s-l-base').remove();
    $('.s-l-opener').remove();
    $('.sortableLists').unbind().removeData();

    if($('.sortableLists').length > 0) {
        $('.sortableLists').sortableLists({
            maxLevels: 2,
            hintClass: 'hint',
            placeholderClass: 'placeholder',
            ignoreClass: 'no-sort',
            insertZonePlus: true,
            opener: {
                active: true,
                as: 'html',
                close: '<i class="far fa-minus-square"></i>',
                open: '<i class="far fa-plus-square"></i>',
                openerCss: {
                    'width': '18px',
                    'height': '18px',
                    'margin-left': '-17px',
                    'margin-right': '-2px',
                },
            },
            isAllowed: function(currEl, hint, target){
                if((!currEl.data('category') && target.data('category')) || target.length === 0) {
                    hint.css('background-color', 'rgba(27, 185, 52, 0.1)');
                    return true;
                }else{
                    hint.css('background-color', 'rgba(237, 28, 36, 0.1)');
                    return false;
                }
            },
            onChange: function (cEl) {
                var $sorter = $(cEl).parents('.sortableLists');
                var listid = $sorter.data('listid');
                var url = $sorter.data('url');
                if(!url){
                    var list = $sorter.data('list');
                    url = "/ajax/listHandler/" + list + "/sort/";
                }

                $.ajax({
                    type: "POST",
                    url: url,
                    data: JSON.stringify({listid: listid, items: $sorter.sortableListsToArray()}),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    success: function (data) {
                        processJSONResponse(data);
                    },
                });
            }
        });
    }
}

function initInfiniteScroll() {
    if ($('.infinite_scroll').length > 0) {
        $(window).scroll(function () {
            var pageBottom = $(window).scrollTop() + $(window).height() + 400;
            $('.infinite_scroll:visible:not(.loading)').each(function(){
                var offsetTop = $(this).offset().top;
                if (offsetTop < pageBottom) {
                    var current = parseInt($(this).data('current'));
                    var pagenum = parseInt($(this).data('pagenum'));
                    if (pagenum > current) {
                        $(this).addClass('loading');
                        current = current * 1 + 1;
                        $(this).data('current', current);
                        if ($(this).data('callback')) {
                            executeFunctionByName($(this).data('callback'), window, this);
                        } else if ($(this).data('url')) {
                            $.ajax({
                                url: $(this).data('url') + current,
                                success: $.proxy(processInfiniteScroll, this)
                            });
                        }
                    } else {
                        $(this).hide();
                    }
                }
            });
        });
        $(window).scroll();
    }
}

function executeFunctionByName(functionName, context /*, args */) {
    var args = Array.prototype.slice.call(arguments, 2);
    var namespaces = functionName.split(".");
    var func = namespaces.pop();
    for (var i = 0; i < namespaces.length; i++) {
        context = context[namespaces[i]];
    }
    return context[func].apply(context, args);
}

function processInfiniteScroll(data) {
    $( $(this).data('container') ).append(data);
    $(this).removeClass('loading');
}

function resetInfiniteScroll(id, pagenum) {
    var $scroll = $('#' + id);
    $scroll.removeClass('loading').data('current', 1).data('pagenum', pagenum);
    if (pagenum > 1) {
        $scroll.show();
    } else {
        $scroll.hide();
    }
};var tables = {
    inProgress: false,

    setProgress: function(on){
        tables.inProgress = on;
    },

    sendRequest: function(table, keyValues, action, params){
        if(keyValues === '' || !keyValues) keyValues = 0;
        var alias = false;
        var options = false;
        var $table = $('#table_' + table);

        tables.setProgress(true);

        if($table.length > 0){
            alias = $table.data('alias');
            table = $table.data('table');
            options = $table.data('options');
        }

        var url = table + '/' + keyValues + '/';
        if (arguments.length > 4) {
            for(var i = 4; i < arguments.length; i++) {
                url += arguments[i] + '/';
            }
        }

        $.ajax({
            method: "GET",
            url: '/ajax/tables/' + url,
            data: {
                alias: alias,
                action: action,
                params: params,
                options: options
            }
        }).done(function (data) {
            if(typeof data !== 'object'){
                data = JSON.parse(data);
            }
            for (var selector in data) {
                $(selector).replaceWith(data[selector]);
            }

            app.reInit();
            tables.reInit();

            tables.setProgress(false);
        });
    },

    checkBox: function(table, keyValues, field, value, method){
        if(method !== 'mark'){
            method = 'check';
        }
        tables.sendRequest(table, keyValues, method, {'field': field, 'value': value});
    },

    page: function(table, keyValues, page){
        tables.sendRequest(table, keyValues, 'page', {'page': page});
    },

    delete: function(table, keyValues){
        $('#confirm-delete').modal('hide');
        tables.sendRequest(table, keyValues, 'delete');
    },

    unDelete: function(table, keyValues){
        $('#confirm-delete').modal('hide');
        tables.sendRequest(table, keyValues, 'undelete');
    },

    copy: function(table, keyValues){
        $('#confirm-delete').modal('hide');
        tables.sendRequest(table, keyValues, 'copy');
    },

    action: function(table, keyValues, action, params){
        $('#confirm-delete').modal('hide');
        tables.sendRequest(table, keyValues, action, params);
    },

    reloadTable: function(params){
        tables.reload(params[0], params[1], params[2]);
    },

    reload: function(table, keyValues, closeModal){
        closeModal = typeof closeModal !== 'undefined' ? closeModal : true;

        if(closeModal) {
            $('#ajax-modal').modal('hide');
        }

        tables.sendRequest(table, keyValues, 'reload');
    },

    initControls: function(){
        $(document).on('click', '.tr-clickable', function(e){
            if($(this).data('modal')){
                var $modal = $('#ajax-modal');
                $modal.find('.modal-dialog').addClass('modal-' + $(this).data('size'));
                $modal.find('.modal-content').load($(this).data('href'));
                $modal.modal('show');
            }else if($(this).data('url')){
                if($(this).data('target') === 'self'){
                    document.location = $(this).data('url');
                }else {
                    window.open($(this).data('url'));
                }
            }else{
                var $modal = $("a[data-toggle='modal'] i");
                if (!$(e.target).is($modal)) {
                    window.location.href += $(this).data('edit');
                }
            }
        });

        $('.td-clickable').on('click', function (e) {
            var $this = $(this).parent('tr');
            var page = $this.data('url');
            document.location = page;
        });

        $(document).on('click', '.btn-table-pager', function(e){
            var $this = $(this);
            var table = $this.parents('.pagination').data('table');
            var keyValues = $this.parents('.pagination').data('keyvalues');
            var page = $this.data('page');

            if(!$this.hasClass('disabled') && !$this.hasClass('active')){
                tables.page(table, keyValues, page);
            }
        });

        $(document).on('click', '.table-options, .table-check', function(e){
            e.stopImmediatePropagation();
        });

        $(document).on('click', '.table-check input[type=checkbox]', function(e){
            var $this = $(this);
            e.stopImmediatePropagation();

            var checked = ($this.is(':checked')) ? 1 : 0;
            tables.checkBox($this.data('table'), $this.data('keyvalue'), $this.data('field'), checked, $this.data('method'));
        });
    },

    reInit: function(){

    },

    init: function(){
        this.initControls();
        this.reInit();
    }
};

$(function() {
    tables.init();
});
