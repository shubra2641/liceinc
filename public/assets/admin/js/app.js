/*! For license information please see app.js.LICENSE.txt */
// security-ignore: COMPILED_JS (Minified library code)
(() => {
  'use strict';
  var t = {
    d: (e, i) => {
      for (const n in i) {
        t.o(i, n) &&
            !t.o(e, n) &&
            Object.defineProperty(e, n, { enumerable: !0, get: i[n] });
      }
    },
    o: (t, e) => Object.prototype.hasOwnProperty.call(t, e),
    r: t => {
      (typeof Symbol !== 'undefined' &&
          Symbol.toStringTag &&
          Object.defineProperty(t, Symbol.toStringTag, { value: 'Module' }),
      Object.defineProperty(t, '__esModule', { value: !0 }));
    },
  };
  const e = {};
  (t.r(e),
  t.d(e, {
    afterMain: () => w,
    afterRead: () => b,
    afterWrite: () => C,
    applyStyles: () => D,
    arrow: () => G,
    auto: () => r,
    basePlacements: () => a,
    beforeMain: () => v,
    beforeRead: () => g,
    beforeWrite: () => A,
    bottom: () => n,
    clippingParents: () => h,
    computeStyles: () => et,
    createPopper: () => Dt,
    createPopperBase: () => St,
    createPopperLite: () => $t,
    detectOverflow: () => _t,
    end: () => c,
    eventListeners: () => nt,
    flip: () => bt,
    hide: () => wt,
    left: () => o,
    main: () => y,
    modifierPhases: () => T,
    offset: () => At,
    placements: () => m,
    popper: () => d,
    popperGenerator: () => Lt,
    popperOffsets: () => Et,
    preventOverflow: () => Ct,
    read: () => _,
    reference: () => f,
    right: () => s,
    start: () => l,
    top: () => i,
    variationPlacements: () => p,
    viewport: () => u,
    write: () => E,
  }));
  var i = 'top';
  var n = 'bottom';
  var s = 'right';
  var o = 'left';
  var r = 'auto';
  var a = [i, n, s, o];
  var l = 'start';
  var c = 'end';
  var h = 'clippingParents';
  var u = 'viewport';
  var d = 'popper';
  var f = 'reference';
  var p = a.reduce((t, e) => t.concat([`${e}-${l}`, `${e}-${c}`]), []);
  var m = [].concat(a, [r]).reduce((t, e) => t.concat([e, `${e}-${l}`, `${e}-${c}`]), []);
  var g = 'beforeRead';
  var _ = 'read';
  var b = 'afterRead';
  var v = 'beforeMain';
  var y = 'main';
  var w = 'afterMain';
  var A = 'beforeWrite';
  var E = 'write';
  var C = 'afterWrite';
  var T = [g, _, b, v, y, w, A, E, C];
  function O(t) {
    return t ? (t.nodeName || '').toLowerCase() : null;
  }
  function x(t) {
    if (t == null) {
      return window;
    }
    if (t.toString() !== '[object Window]') {
      const e = t.ownerDocument;
      return (e && e.defaultView) || window;
    }
    return t;
  }
  function k(t) {
    return t instanceof x(t).Element || t instanceof Element;
  }
  function L(t) {
    return t instanceof x(t).HTMLElement || t instanceof HTMLElement;
  }
  function S(t) {
    return (
      typeof ShadowRoot !== 'undefined' &&
      (t instanceof x(t).ShadowRoot || t instanceof ShadowRoot)
    );
  }
  const D = {
    name: 'applyStyles',
    enabled: !0,
    phase: 'write',
    fn: function(t) {
      const e = t.state;
      Object.keys(e.elements).forEach(t => {
        const i = e.styles[t] || {};
        const n = e.attributes[t] || {};
        const s = e.elements[t];
        L(s) &&
          O(s) &&
          (Object.assign(s.style, i),
          Object.keys(n).forEach(t => {
            const e = n[t];
            !1 === e ?
              s.removeAttribute(t) :
              s.setAttribute(t, !0 === e ? '' : e);
          }));
      });
    },
    effect: function(t) {
      const e = t.state;
      const i = {
        popper: {
          position: e.options.strategy,
          left: '0',
          top: '0',
          margin: '0',
        },
        arrow: { position: 'absolute' },
        reference: {},
      };
      return (
        Object.assign(e.elements.popper.style, i.popper),
        (e.styles = i),
        e.elements.arrow && Object.assign(e.elements.arrow.style, i.arrow),
        function() {
          Object.keys(e.elements).forEach(t => {
            const n = e.elements[t];
            const s = e.attributes[t] || {};
            const o = Object.keys(
              e.styles.hasOwnProperty(t) ? e.styles[t] : i[t],
            ).reduce((t, e) => ((t[e] = ''), t), {});
            L(n) &&
              O(n) &&
              (Object.assign(n.style, o),
              Object.keys(s).forEach(t => {
                n.removeAttribute(t);
              }));
          });
        }
      );
    },
    requires: ['computeStyles'],
  };
  function $(t) {
    return t.split('-')[0];
  }
  const I = Math.max;
  const N = Math.min;
  const P = Math.round;
  function M() {
    const t = navigator.userAgentData;
    return t != null && t.brands && Array.isArray(t.brands) ?
      t.brands
        .map(t => `${t.brand}/${t.version}`)
        .join(' ') :
      navigator.userAgent;
  }
  function j() {
    return !/^((?!chrome|android).)*safari/i.test(M());
  }
  function F(t, e, i) {
    (void 0 === e && (e = !1), void 0 === i && (i = !1));
    const n = t.getBoundingClientRect();
    let s = 1;
    let o = 1;
    e &&
      L(t) &&
      ((s = (t.offsetWidth > 0 && P(n.width) / t.offsetWidth) || 1),
      (o = (t.offsetHeight > 0 && P(n.height) / t.offsetHeight) || 1));
    const r = (k(t) ? x(t) : window).visualViewport;
    const a = !j() && i;
    const l = (n.left + (a && r ? r.offsetLeft : 0)) / s;
    const c = (n.top + (a && r ? r.offsetTop : 0)) / o;
    const h = n.width / s;
    const u = n.height / o;
    return {
      width: h,
      height: u,
      top: c,
      right: l + h,
      bottom: c + u,
      left: l,
      x: l,
      y: c,
    };
  }
  function H(t) {
    const e = F(t);
    let i = t.offsetWidth;
    let n = t.offsetHeight;
    return (
      Math.abs(e.width - i) <= 1 && (i = e.width),
      Math.abs(e.height - n) <= 1 && (n = e.height),
      { x: t.offsetLeft, y: t.offsetTop, width: i, height: n }
    );
  }
  function W(t, e) {
    const i = e.getRootNode && e.getRootNode();
    if (t.contains(e)) {
      return !0;
    }
    if (i && S(i)) {
      let n = e;
      do {
        if (n && t.isSameNode(n)) {
          return !0;
        }
        n = n.parentNode || n.host;
      } while (n);
    }
    return !1;
  }
  function B(t) {
    return x(t).getComputedStyle(t);
  }
  function z(t) {
    return ['table', 'td', 'th'].indexOf(O(t)) >= 0;
  }
  function R(t) {
    return ((k(t) ? t.ownerDocument : t.document) || window.document)
      .documentElement;
  }
  function q(t) {
    return O(t) === 'html' ?
      t :
      t.assignedSlot || t.parentNode || (S(t) ? t.host : null) || R(t);
  }
  function V(t) {
    return L(t) && B(t).position !== 'fixed' ? t.offsetParent : null;
  }
  function K(t) {
    for (var e = x(t), i = V(t); i && z(i) && B(i).position === 'static';) {
      i = V(i);
    }
    return i &&
      (O(i) === 'html' || (O(i) === 'body' && B(i).position === 'static')) ?
      e :
      i ||
          (function(t) {
            const e = /firefox/i.test(M());
            if (/Trident/i.test(M()) && L(t) && B(t).position === 'fixed') {
              return null;
            }
            let i = q(t);
            for (
              S(i) && (i = i.host);
              L(i) && ['html', 'body'].indexOf(O(i)) < 0;

            ) {
              const n = B(i);
              if (
                n.transform !== 'none' ||
                n.perspective !== 'none' ||
                n.contain === 'paint' ||
                ['transform', 'perspective'].indexOf(n.willChange) !== -1 ||
                (e && n.willChange === 'filter') ||
                (e && n.filter && n.filter !== 'none')
              ) {
                return i;
              }
              i = i.parentNode;
            }
            return null;
          })(t) ||
          e;
  }
  function Q(t) {
    return ['top', 'bottom'].indexOf(t) >= 0 ? 'x' : 'y';
  }
  function X(t, e, i) {
    return I(t, N(e, i));
  }
  function Y(t) {
    return Object.assign({}, { top: 0, right: 0, bottom: 0, left: 0 }, t);
  }
  function U(t, e) {
    return e.reduce((e, i) => ((e[i] = t), e), {});
  }
  const G = {
    name: 'arrow',
    enabled: !0,
    phase: 'main',
    fn: function(t) {
      let e;
      const r = t.state;
      const l = t.name;
      const c = t.options;
      const h = r.elements.arrow;
      const u = r.modifiersData.popperOffsets;
      const d = $(r.placement);
      const f = Q(d);
      const p = [o, s].indexOf(d) >= 0 ? 'height' : 'width';
      if (h && u) {
        const m = (function(t, e) {
          return Y(
            typeof (t =
                  typeof t === 'function' ?
                    t(Object.assign({}, e.rects, { placement: e.placement })) :
                    t) !==
                'number' ?
              t :
              U(t, a),
          );
        })(c.padding, r);
        const g = H(h);
        const _ = f === 'y' ? i : o;
        const b = f === 'y' ? n : s;
        const v =
            r.rects.reference[p] +
            r.rects.reference[f] -
            u[f] -
            r.rects.popper[p];
        const y = u[f] - r.rects.reference[f];
        const w = K(h);
        const A = w ? (f === 'y' ? w.clientHeight || 0 : w.clientWidth || 0) : 0;
        const E = v / 2 - y / 2;
        const C = m[_];
        const T = A - g[p] - m[b];
        const O = A / 2 - g[p] / 2 + E;
        const x = X(C, O, T);
        const k = f;
        r.modifiersData[l] = (((e = {})[k] = x), (e.centerOffset = x - O), e);
      }
    },
    effect: function(t) {
      const e = t.state;
      const i = t.options.element;
      let n = void 0 === i ? '[data-popper-arrow]' : i;
      n != null &&
        (typeof n !== 'string' || (n = e.elements.popper.querySelector(n))) &&
        W(e.elements.popper, n) &&
        (e.elements.arrow = n);
    },
    requires: ['popperOffsets'],
    requiresIfExists: ['preventOverflow'],
  };
  function J(t) {
    return t.split('-')[1];
  }
  const Z = { top: 'auto', right: 'auto', bottom: 'auto', left: 'auto' };
  function tt(t) {
    let e;
    const r = t.popper;
    const a = t.popperRect;
    const l = t.placement;
    const h = t.variation;
    const u = t.offsets;
    const d = t.position;
    const f = t.gpuAcceleration;
    const p = t.adaptive;
    const m = t.roundOffsets;
    const g = t.isFixed;
    const _ = u.x;
    let b = void 0 === _ ? 0 : _;
    const v = u.y;
    let y = void 0 === v ? 0 : v;
    const w = typeof m === 'function' ? m({ x: b, y }) : { x: b, y };
    ((b = w.x), (y = w.y));
    const A = u.hasOwnProperty('x');
    const E = u.hasOwnProperty('y');
    let C = o;
    let T = i;
    const O = window;
    if (p) {
      let k = K(r);
      let L = 'clientHeight';
      let S = 'clientWidth';
      if (
        (k === x(r) &&
          B((k = R(r))).position !== 'static' &&
          d === 'absolute' &&
          ((L = 'scrollHeight'), (S = 'scrollWidth')),
        l === i || ((l === o || l === s) && h === c))
      ) {
        ((T = n),
        (y -=
            (g && k === O && O.visualViewport ?
              O.visualViewport.height :
              k[L]) - a.height),
        (y *= f ? 1 : -1));
      }
      if (l === o || ((l === i || l === n) && h === c)) {
        ((C = s),
        (b -=
            (g && k === O && O.visualViewport ? O.visualViewport.width : k[S]) -
            a.width),
        (b *= f ? 1 : -1));
      }
    }
    let D;
    const $ = Object.assign({ position: d }, p && Z);
    const I =
        !0 === m ?
          (function(t, e) {
            const i = t.x;
            const n = t.y;
            const s = e.devicePixelRatio || 1;
            return { x: P(i * s) / s || 0, y: P(n * s) / s || 0 };
          })({ x: b, y }, x(r)) :
          { x: b, y };
    return (
      (b = I.x),
      (y = I.y),
      f ?
        Object.assign(
          {},
          $,
          (((D = {})[T] = E ? '0' : ''),
          (D[C] = A ? '0' : ''),
          (D.transform =
              (O.devicePixelRatio || 1) <= 1 ?
                `translate(${b}px, ${y}px)` :
                `translate3d(${b}px, ${y}px, 0)`),
          D),
        ) :
        Object.assign(
          {},
          $,
          (((e = {})[T] = E ? `${y}px` : ''),
          (e[C] = A ? `${b}px` : ''),
          (e.transform = ''),
          e),
        )
    );
  }
  const et = {
    name: 'computeStyles',
    enabled: !0,
    phase: 'beforeWrite',
    fn: function(t) {
      const e = t.state;
      const i = t.options;
      const n = i.gpuAcceleration;
      const s = void 0 === n || n;
      const o = i.adaptive;
      const r = void 0 === o || o;
      const a = i.roundOffsets;
      const l = void 0 === a || a;
      const c = {
        placement: $(e.placement),
        variation: J(e.placement),
        popper: e.elements.popper,
        popperRect: e.rects.popper,
        gpuAcceleration: s,
        isFixed: e.options.strategy === 'fixed',
      };
      (e.modifiersData.popperOffsets != null &&
        (e.styles.popper = Object.assign(
          {},
          e.styles.popper,
          tt(
            Object.assign({}, c, {
              offsets: e.modifiersData.popperOffsets,
              position: e.options.strategy,
              adaptive: r,
              roundOffsets: l,
            }),
          ),
        )),
      e.modifiersData.arrow != null &&
          (e.styles.arrow = Object.assign(
            {},
            e.styles.arrow,
            tt(
              Object.assign({}, c, {
                offsets: e.modifiersData.arrow,
                position: 'absolute',
                adaptive: !1,
                roundOffsets: l,
              }),
            ),
          )),
      (e.attributes.popper = Object.assign({}, e.attributes.popper, {
        'data-popper-placement': e.placement,
      })));
    },
    data: {},
  };
  const it = { passive: !0 };
  const nt = {
    name: 'eventListeners',
    enabled: !0,
    phase: 'write',
    fn: function() {},
    effect: function(t) {
      const e = t.state;
      const i = t.instance;
      const n = t.options;
      const s = n.scroll;
      const o = void 0 === s || s;
      const r = n.resize;
      const a = void 0 === r || r;
      const l = x(e.elements.popper);
      const c = [].concat(e.scrollParents.reference, e.scrollParents.popper);
      return (
        o &&
          c.forEach(t => {
            t.addEventListener('scroll', i.update, it);
          }),
        a && l.addEventListener('resize', i.update, it),
        function() {
          (o &&
            c.forEach(t => {
              t.removeEventListener('scroll', i.update, it);
            }),
          a && l.removeEventListener('resize', i.update, it));
        }
      );
    },
    data: {},
  };
  const st = { left: 'right', right: 'left', bottom: 'top', top: 'bottom' };
  function ot(t) {
    return t.replace(/left|right|bottom|top/g, t => st[t]);
  }
  const rt = { start: 'end', end: 'start' };
  function at(t) {
    return t.replace(/start|end/g, t => rt[t]);
  }
  function lt(t) {
    const e = x(t);
    return { scrollLeft: e.pageXOffset, scrollTop: e.pageYOffset };
  }
  function ct(t) {
    return F(R(t)).left + lt(t).scrollLeft;
  }
  function ht(t) {
    const e = B(t);
    const i = e.overflow;
    const n = e.overflowX;
    const s = e.overflowY;
    return /auto|scroll|overlay|hidden/.test(i + s + n);
  }
  function ut(t) {
    return ['html', 'body', '#document'].indexOf(O(t)) >= 0 ?
      t.ownerDocument.body :
      L(t) && ht(t) ?
        t :
        ut(q(t));
  }
  function dt(t, e) {
    let i;
    void 0 === e && (e = []);
    const n = ut(t);
    const s = n === ((i = t.ownerDocument) == null ? void 0 : i.body);
    const o = x(n);
    const r = s ? [o].concat(o.visualViewport || [], ht(n) ? n : []) : n;
    const a = e.concat(r);
    return s ? a : a.concat(dt(q(r)));
  }
  function ft(t) {
    return Object.assign({}, t, {
      left: t.x,
      top: t.y,
      right: t.x + t.width,
      bottom: t.y + t.height,
    });
  }
  function pt(t, e, i) {
    return e === u ?
      ft(
        (function(t, e) {
          const i = x(t);
          const n = R(t);
          const s = i.visualViewport;
          let o = n.clientWidth;
          let r = n.clientHeight;
          let a = 0;
          let l = 0;
          if (s) {
            ((o = s.width), (r = s.height));
            const c = j();
            (c || (!c && e === 'fixed')) &&
                ((a = s.offsetLeft), (l = s.offsetTop));
          }
          return { width: o, height: r, x: a + ct(t), y: l };
        })(t, i),
      ) :
      k(e) ?
        (function(t, e) {
          const i = F(t, !1, e === 'fixed');
          return (
            (i.top = i.top + t.clientTop),
            (i.left = i.left + t.clientLeft),
            (i.bottom = i.top + t.clientHeight),
            (i.right = i.left + t.clientWidth),
            (i.width = t.clientWidth),
            (i.height = t.clientHeight),
            (i.x = i.left),
            (i.y = i.top),
            i
          );
        })(e, i) :
        ft(
          (function(t) {
            let e;
            const i = R(t);
            const n = lt(t);
            const s = (e = t.ownerDocument) == null ? void 0 : e.body;
            const o = I(
              i.scrollWidth,
              i.clientWidth,
              s ? s.scrollWidth : 0,
              s ? s.clientWidth : 0,
            );
            const r = I(
              i.scrollHeight,
              i.clientHeight,
              s ? s.scrollHeight : 0,
              s ? s.clientHeight : 0,
            );
            let a = -n.scrollLeft + ct(t);
            const l = -n.scrollTop;
            return (
              B(s || i).direction === 'rtl' &&
                  (a += I(i.clientWidth, s ? s.clientWidth : 0) - o),
              { width: o, height: r, x: a, y: l }
            );
          })(R(t)),
        );
  }
  function mt(t, e, i, n) {
    const s =
        e === 'clippingParents' ?
          (function(t) {
            const e = dt(q(t));
            const i =
                  ['absolute', 'fixed'].indexOf(B(t).position) >= 0 && L(t) ?
                    K(t) :
                    t;
            return k(i) ?
              e.filter(t => k(t) && W(t, i) && O(t) !== 'body') :
              [];
          })(t) :
          [].concat(e);
    const o = [].concat(s, [i]);
    const r = o[0];
    const a = o.reduce(
      (e, i) => {
        const s = pt(t, i, n);
        return (
          (e.top = I(s.top, e.top)),
          (e.right = N(s.right, e.right)),
          (e.bottom = N(s.bottom, e.bottom)),
          (e.left = I(s.left, e.left)),
          e
        );
      },
      pt(t, r, n),
    );
    return (
      (a.width = a.right - a.left),
      (a.height = a.bottom - a.top),
      (a.x = a.left),
      (a.y = a.top),
      a
    );
  }
  function gt(t) {
    let e;
    const r = t.reference;
    const a = t.element;
    const h = t.placement;
    const u = h ? $(h) : null;
    const d = h ? J(h) : null;
    const f = r.x + r.width / 2 - a.width / 2;
    const p = r.y + r.height / 2 - a.height / 2;
    switch (u) {
    case i:
      e = { x: f, y: r.y - a.height };
      break;
    case n:
      e = { x: f, y: r.y + r.height };
      break;
    case s:
      e = { x: r.x + r.width, y: p };
      break;
    case o:
      e = { x: r.x - a.width, y: p };
      break;
    default:
      e = { x: r.x, y: r.y };
    }
    const m = u ? Q(u) : null;
    if (m != null) {
      const g = m === 'y' ? 'height' : 'width';
      switch (d) {
      case l:
        e[m] = e[m] - (r[g] / 2 - a[g] / 2);
        break;
      case c:
        e[m] = e[m] + (r[g] / 2 - a[g] / 2);
      }
    }
    return e;
  }
  function _t(t, e) {
    void 0 === e && (e = {});
    const o = e;
    const r = o.placement;
    const l = void 0 === r ? t.placement : r;
    const c = o.strategy;
    const p = void 0 === c ? t.strategy : c;
    const m = o.boundary;
    const g = void 0 === m ? h : m;
    const _ = o.rootBoundary;
    const b = void 0 === _ ? u : _;
    const v = o.elementContext;
    const y = void 0 === v ? d : v;
    const w = o.altBoundary;
    const A = void 0 !== w && w;
    const E = o.padding;
    const C = void 0 === E ? 0 : E;
    const T = Y(typeof C !== 'number' ? C : U(C, a));
    const O = y === d ? f : d;
    const x = t.rects.popper;
    const L = t.elements[A ? O : y];
    const S = mt(k(L) ? L : L.contextElement || R(t.elements.popper), g, b, p);
    const D = F(t.elements.reference);
    const $ = gt({ reference: D, element: x, strategy: 'absolute', placement: l });
    const I = ft(Object.assign({}, x, $));
    const N = y === d ? I : D;
    const P = {
      top: S.top - N.top + T.top,
      bottom: N.bottom - S.bottom + T.bottom,
      left: S.left - N.left + T.left,
      right: N.right - S.right + T.right,
    };
    const M = t.modifiersData.offset;
    if (y === d && M) {
      const j = M[l];
      Object.keys(P).forEach(t => {
        const e = [s, n].indexOf(t) >= 0 ? 1 : -1;
        const o = [i, n].indexOf(t) >= 0 ? 'y' : 'x';
        P[t] += j[o] * e;
      });
    }
    return P;
  }
  const bt = {
    name: 'flip',
    enabled: !0,
    phase: 'main',
    fn: function(t) {
      const e = t.state;
      const c = t.options;
      const h = t.name;
      if (!e.modifiersData[h]._skip) {
        for (
          var u = c.mainAxis,
            d = void 0 === u || u,
            f = c.altAxis,
            g = void 0 === f || f,
            _ = c.fallbackPlacements,
            b = c.padding,
            v = c.boundary,
            y = c.rootBoundary,
            w = c.altBoundary,
            A = c.flipVariations,
            E = void 0 === A || A,
            C = c.allowedAutoPlacements,
            T = e.options.placement,
            O = $(T),
            x =
              _ ||
              (O === T || !E ?
                [ot(T)] :
                (function(t) {
                  if ($(t) === r) {
                    return [];
                  }
                  const e = ot(t);
                  return [at(t), e, at(e)];
                })(T)),
            k = [T].concat(x).reduce((t, i) => t.concat(
              $(i) === r ?
                (function(t, e) {
                  void 0 === e && (e = {});
                  const i = e;
                  const n = i.placement;
                  const s = i.boundary;
                  const o = i.rootBoundary;
                  const r = i.padding;
                  const l = i.flipVariations;
                  const c = i.allowedAutoPlacements;
                  const h = void 0 === c ? m : c;
                  const u = J(n);
                  const d = u ?
                    l ?
                      p :
                      p.filter(t => J(t) === u) :
                    a;
                  let f = d.filter(t => h.indexOf(t) >= 0);
                  f.length === 0 && (f = d);
                  const g = f.reduce((e, i) => (
                    (e[i] = _t(t, {
                      placement: i,
                      boundary: s,
                      rootBoundary: o,
                      padding: r,
                    })[$(i)]),
                    e
                  ), {});
                  return Object.keys(g).sort((t, e) => g[t] - g[e]);
                })(e, {
                  placement: i,
                  boundary: v,
                  rootBoundary: y,
                  padding: b,
                  flipVariations: E,
                  allowedAutoPlacements: C,
                }) :
                i,
            ), []),
            L = e.rects.reference,
            S = e.rects.popper,
            D = new Map(),
            I = !0,
            N = k[0],
            P = 0;
          P < k.length;
          P++
        ) {
          const M = k[P];
          const j = $(M);
          const F = J(M) === l;
          const H = [i, n].indexOf(j) >= 0;
          const W = H ? 'width' : 'height';
          const B = _t(e, {
            placement: M,
            boundary: v,
            rootBoundary: y,
            altBoundary: w,
            padding: b,
          });
          let z = H ? (F ? s : o) : F ? n : i;
          L[W] > S[W] && (z = ot(z));
          const R = ot(z);
          const q = [];
          if (
            (d && q.push(B[j] <= 0),
            g && q.push(B[z] <= 0, B[R] <= 0),
            q.every(t => t))
          ) {
            ((N = M), (I = !1));
            break;
          }
          D.set(M, q);
        }
        if (I) {
          for (
            let V = function(t) {
                const e = k.find(e => {
                  const i = D.get(e);
                  if (i) {
                    return i.slice(0, t).every(t => t);
                  }
                });
                if (e) {
                  return ((N = e), 'break');
                }
              },
              K = E ? 3 : 1;
            K > 0;
            K--
          ) {
            if (V(K) === 'break') {
              break;
            }
          }
        }
        e.placement !== N &&
          ((e.modifiersData[h]._skip = !0), (e.placement = N), (e.reset = !0));
      }
    },
    requiresIfExists: ['offset'],
    data: { _skip: !1 },
  };
  function vt(t, e, i) {
    return (
      void 0 === i && (i = { x: 0, y: 0 }),
      {
        top: t.top - e.height - i.y,
        right: t.right - e.width + i.x,
        bottom: t.bottom - e.height + i.y,
        left: t.left - e.width - i.x,
      }
    );
  }
  function yt(t) {
    return [i, s, n, o].some(e => t[e] >= 0);
  }
  const wt = {
    name: 'hide',
    enabled: !0,
    phase: 'main',
    requiresIfExists: ['preventOverflow'],
    fn: function(t) {
      const e = t.state;
      const i = t.name;
      const n = e.rects.reference;
      const s = e.rects.popper;
      const o = e.modifiersData.preventOverflow;
      const r = _t(e, { elementContext: 'reference' });
      const a = _t(e, { altBoundary: !0 });
      const l = vt(r, n);
      const c = vt(a, s, o);
      const h = yt(l);
      const u = yt(c);
      ((e.modifiersData[i] = {
        referenceClippingOffsets: l,
        popperEscapeOffsets: c,
        isReferenceHidden: h,
        hasPopperEscaped: u,
      }),
      (e.attributes.popper = Object.assign({}, e.attributes.popper, {
        'data-popper-reference-hidden': h,
        'data-popper-escaped': u,
      })));
    },
  };
  const At = {
    name: 'offset',
    enabled: !0,
    phase: 'main',
    requires: ['popperOffsets'],
    fn: function(t) {
      const e = t.state;
      const n = t.options;
      const r = t.name;
      const a = n.offset;
      const l = void 0 === a ? [0, 0] : a;
      const c = m.reduce((t, n) => (
        (t[n] = (function(t, e, n) {
          const r = $(t);
          const a = [o, i].indexOf(r) >= 0 ? -1 : 1;
          const l =
                  typeof n === 'function' ?
                    n(Object.assign({}, e, { placement: t })) :
                    n;
          let c = l[0];
          let h = l[1];
          return (
            (c = c || 0),
            (h = (h || 0) * a),
            [o, s].indexOf(r) >= 0 ? { x: h, y: c } : { x: c, y: h }
          );
        })(n, e.rects, l)),
        t
      ), {});
      const h = c[e.placement];
      const u = h.x;
      const d = h.y;
      (e.modifiersData.popperOffsets != null &&
        ((e.modifiersData.popperOffsets.x += u),
        (e.modifiersData.popperOffsets.y += d)),
      (e.modifiersData[r] = c));
    },
  };
  const Et = {
    name: 'popperOffsets',
    enabled: !0,
    phase: 'read',
    fn: function(t) {
      const e = t.state;
      const i = t.name;
      e.modifiersData[i] = gt({
        reference: e.rects.reference,
        element: e.rects.popper,
        strategy: 'absolute',
        placement: e.placement,
      });
    },
    data: {},
  };
  const Ct = {
    name: 'preventOverflow',
    enabled: !0,
    phase: 'main',
    fn: function(t) {
      const e = t.state;
      const r = t.options;
      const a = t.name;
      const c = r.mainAxis;
      const h = void 0 === c || c;
      const u = r.altAxis;
      const d = void 0 !== u && u;
      const f = r.boundary;
      const p = r.rootBoundary;
      const m = r.altBoundary;
      const g = r.padding;
      const _ = r.tether;
      const b = void 0 === _ || _;
      const v = r.tetherOffset;
      const y = void 0 === v ? 0 : v;
      const w = _t(e, { boundary: f, rootBoundary: p, padding: g, altBoundary: m });
      const A = $(e.placement);
      const E = J(e.placement);
      const C = !E;
      const T = Q(A);
      const O = T === 'x' ? 'y' : 'x';
      const x = e.modifiersData.popperOffsets;
      const k = e.rects.reference;
      const L = e.rects.popper;
      const S =
          typeof y === 'function' ?
            y(Object.assign({}, e.rects, { placement: e.placement })) :
            y;
      const D =
          typeof S === 'number' ?
            { mainAxis: S, altAxis: S } :
            Object.assign({ mainAxis: 0, altAxis: 0 }, S);
      const P = e.modifiersData.offset ? e.modifiersData.offset[e.placement] : null;
      const M = { x: 0, y: 0 };
      if (x) {
        if (h) {
          let j;
          const F = T === 'y' ? i : o;
          const W = T === 'y' ? n : s;
          const B = T === 'y' ? 'height' : 'width';
          const z = x[T];
          const R = z + w[F];
          const q = z - w[W];
          const V = b ? -L[B] / 2 : 0;
          const Y = E === l ? k[B] : L[B];
          const U = E === l ? -L[B] : -k[B];
          const G = e.elements.arrow;
          const Z = b && G ? H(G) : { width: 0, height: 0 };
          const tt = e.modifiersData['arrow#persistent'] ?
            e.modifiersData['arrow#persistent'].padding :
            { top: 0, right: 0, bottom: 0, left: 0 };
          const et = tt[F];
          const it = tt[W];
          const nt = X(0, k[B], Z[B]);
          const st = C ?
            k[B] / 2 - V - nt - et - D.mainAxis :
            Y - nt - et - D.mainAxis;
          const ot = C ?
            -k[B] / 2 + V + nt + it + D.mainAxis :
            U + nt + it + D.mainAxis;
          const rt = e.elements.arrow && K(e.elements.arrow);
          const at = rt ? (T === 'y' ? rt.clientTop || 0 : rt.clientLeft || 0) : 0;
          const lt = (j = P == null ? void 0 : P[T]) != null ? j : 0;
          const ct = z + ot - lt;
          const ht = X(b ? N(R, z + st - lt - at) : R, z, b ? I(q, ct) : q);
          ((x[T] = ht), (M[T] = ht - z));
        }
        if (d) {
          let ut;
          const dt = T === 'x' ? i : o;
          const ft = T === 'x' ? n : s;
          const pt = x[O];
          const mt = O === 'y' ? 'height' : 'width';
          const gt = pt + w[dt];
          const bt = pt - w[ft];
          const vt = [i, o].indexOf(A) !== -1;
          const yt = (ut = P == null ? void 0 : P[O]) != null ? ut : 0;
          const wt = vt ? gt : pt - k[mt] - L[mt] - yt + D.altAxis;
          const At = vt ? pt + k[mt] + L[mt] - yt - D.altAxis : bt;
          const Et =
              b && vt ?
                (function(t, e, i) {
                  const n = X(t, e, i);
                  return n > i ? i : n;
                })(wt, pt, At) :
                X(b ? wt : gt, pt, b ? At : bt);
          ((x[O] = Et), (M[O] = Et - pt));
        }
        e.modifiersData[a] = M;
      }
    },
    requiresIfExists: ['offset'],
  };
  function Tt(t, e, i) {
    void 0 === i && (i = !1);
    let n;
    let s;
    const o = L(e);
    const r =
        L(e) &&
        (function(t) {
          const e = t.getBoundingClientRect();
          const i = P(e.width) / t.offsetWidth || 1;
          const n = P(e.height) / t.offsetHeight || 1;
          return i !== 1 || n !== 1;
        })(e);
    const a = R(e);
    const l = F(t, r, i);
    let c = { scrollLeft: 0, scrollTop: 0 };
    let h = { x: 0, y: 0 };
    return (
      (o || (!o && !i)) &&
        ((O(e) !== 'body' || ht(a)) &&
          (c =
            (n = e) !== x(n) && L(n) ?
              { scrollLeft: (s = n).scrollLeft, scrollTop: s.scrollTop } :
              lt(n)),
        L(e) ?
          (((h = F(e, !0)).x += e.clientLeft), (h.y += e.clientTop)) :
          a && (h.x = ct(a))),
      {
        x: l.left + c.scrollLeft - h.x,
        y: l.top + c.scrollTop - h.y,
        width: l.width,
        height: l.height,
      }
    );
  }
  function Ot(t) {
    const e = new Map();
    const i = new Set();
    const n = [];
    function s(t) {
      (i.add(t.name),
      []
        .concat(t.requires || [], t.requiresIfExists || [])
        .forEach(t => {
          if (!i.has(t)) {
            const n = e.get(t);
            n && s(n);
          }
        }),
      n.push(t));
    }
    return (
      t.forEach(t => {
        e.set(t.name, t);
      }),
      t.forEach(t => {
        i.has(t.name) || s(t);
      }),
      n
    );
  }
  const xt = { placement: 'bottom', modifiers: [], strategy: 'absolute' };
  function kt() {
    for (var t = arguments.length, e = new Array(t), i = 0; i < t; i++) {
      e[i] = arguments[i];
    }
    return !e.some(t => !(t && typeof t.getBoundingClientRect === 'function'));
  }
  function Lt(t) {
    void 0 === t && (t = {});
    const e = t;
    const i = e.defaultModifiers;
    const n = void 0 === i ? [] : i;
    const s = e.defaultOptions;
    const o = void 0 === s ? xt : s;
    return function(t, e, i) {
      void 0 === i && (i = o);
      let s;
      let r;
      let a = {
        placement: 'bottom',
        orderedModifiers: [],
        options: Object.assign({}, xt, o),
        modifiersData: {},
        elements: { reference: t, popper: e },
        attributes: {},
        styles: {},
      };
      let l = [];
      let c = !1;
      var h = {
        state: a,
        setOptions: function(i) {
          const s = typeof i === 'function' ? i(a.options) : i;
          (u(),
          (a.options = Object.assign({}, o, a.options, s)),
          (a.scrollParents = {
            reference: k(t) ?
              dt(t) :
              t.contextElement ?
                dt(t.contextElement) :
                [],
            popper: dt(e),
          }));
          let r;
          let c;
          const d = (function(t) {
            const e = Ot(t);
            return T.reduce((t, i) => t.concat(
              e.filter(t => t.phase === i),
            ), []);
          })(
            ((r = [].concat(n, a.options.modifiers)),
            (c = r.reduce((t, e) => {
              const i = t[e.name];
              return (
                (t[e.name] = i ?
                  Object.assign({}, i, e, {
                    options: Object.assign({}, i.options, e.options),
                    data: Object.assign({}, i.data, e.data),
                  }) :
                  e),
                t
              );
            }, {})),
            Object.keys(c).map(t => c[t])),
          );
          return (
            (a.orderedModifiers = d.filter(t => t.enabled)),
            a.orderedModifiers.forEach(t => {
              const e = t.name;
              const i = t.options;
              const n = void 0 === i ? {} : i;
              const s = t.effect;
              if (typeof s === 'function') {
                const o = s({ state: a, name: e, instance: h, options: n });
                const r = function() {};
                l.push(o || r);
              }
            }),
            h.update()
          );
        },
        forceUpdate: function() {
          if (!c) {
            const t = a.elements;
            const e = t.reference;
            const i = t.popper;
            if (kt(e, i)) {
              ((a.rects = {
                reference: Tt(e, K(i), a.options.strategy === 'fixed'),
                popper: H(i),
              }),
              (a.reset = !1),
              (a.placement = a.options.placement),
              a.orderedModifiers.forEach(t => (a.modifiersData[t.name] = Object.assign(
                {},
                t.data,
              ))));
              for (let n = 0; n < a.orderedModifiers.length; n++) {
                if (!0 !== a.reset) {
                  const s = a.orderedModifiers[n];
                  const o = s.fn;
                  const r = s.options;
                  const l = void 0 === r ? {} : r;
                  const u = s.name;
                  typeof o === 'function' &&
                      (a =
                        o({ state: a, options: l, name: u, instance: h }) || a);
                } else {
                  ((a.reset = !1), (n = -1));
                }
              }
            }
          }
        },
        update:
            ((s = function() {
              return new Promise(t => {
                (h.forceUpdate(), t(a));
              });
            }),
            function() {
              return (
                r ||
                  (r = new Promise(t => {
                    Promise.resolve().then(() => {
                      ((r = void 0), t(s()));
                    });
                  })),
                r
              );
            }),
        destroy: function() {
          (u(), (c = !0));
        },
      };
      if (!kt(t, e)) {
        return h;
      }
      function u() {
        (l.forEach(t => t()),
        (l = []));
      }
      return (
        h.setOptions(i).then(t => {
          !c && i.onFirstUpdate && i.onFirstUpdate(t);
        }),
        h
      );
    };
  }
  var St = Lt();
  var Dt = Lt({ defaultModifiers: [nt, Et, et, D, At, bt, Ct, G, wt] });
  var $t = Lt({ defaultModifiers: [nt, Et, et, D] });
  const It = new Map();
  const Nt = {
    set(t, e, i) {
      It.has(t) || It.set(t, new Map());
      const n = It.get(t);
      (n.has(e) || n.size === 0) && n.set(e, i);
    },
    get: (t, e) => (It.has(t) && It.get(t).get(e)) || null,
    remove(t, e) {
      if (!It.has(t)) {
        return;
      }
      const i = It.get(t);
      (i.delete(e), i.size === 0 && It.delete(t));
    },
  };
  const Pt = 'transitionend';
  const Mt = t => (
    t &&
        window.CSS &&
        window.CSS.escape &&
        (t = t.replace(/#([^\s"#']+)/g, (t, e) => `#${CSS.escape(e)}`)),
    t
  );
  const jt = t =>
    t == null ?
      `${t}` :
      Object.prototype.toString
        .call(t)
        .match(/\s([a-z]+)/i)[1]
        .toLowerCase();
  const Ft = t => {
    t.dispatchEvent(new Event(Pt));
  };
  const Ht = t =>
    !(!t || typeof t !== 'object') &&
      (void 0 !== t.jquery && (t = t[0]), void 0 !== t.nodeType);
  const Wt = t =>
    Ht(t) ?
      t.jquery ?
        t[0] :
        t :
      typeof t === 'string' && t.length > 0 ?
        document.querySelector(Mt(t)) :
        null;
  const Bt = t => {
    if (!Ht(t) || t.getClientRects().length === 0) {
      return !1;
    }
    const e =
          getComputedStyle(t).getPropertyValue('visibility') === 'visible';
    const i = t.closest('details:not([open])');
    if (!i) {
      return e;
    }
    if (i !== t) {
      const e = t.closest('summary');
      if (e && e.parentNode !== i) {
        return !1;
      }
      if (e === null) {
        return !1;
      }
    }
    return e;
  };
  const zt = t =>
    !t ||
      t.nodeType !== Node.ELEMENT_NODE ||
      !!t.classList.contains('disabled') ||
      (void 0 !== t.disabled ?
        t.disabled :
        t.hasAttribute('disabled') && t.getAttribute('disabled') !== 'false');
  const Rt = t => {
    if (!document.documentElement.attachShadow) {
      return null;
    }
    if (typeof t.getRootNode === 'function') {
      const e = t.getRootNode();
      return e instanceof ShadowRoot ? e : null;
    }
    return t instanceof ShadowRoot ?
      t :
      t.parentNode ?
        Rt(t.parentNode) :
        null;
  };
  const qt = () => {};
  const Vt = t => {
    t.offsetHeight;
  };
  const Kt = () =>
    window.jQuery && !document.body.hasAttribute('data-bs-no-jquery') ?
      window.jQuery :
      null;
  const Qt = [];
  const Xt = () => document.documentElement.dir === 'rtl';
  const Yt = t => {
    let e;
    ((e = () => {
      const e = Kt();
      if (e) {
        const i = t.NAME;
        const n = e.fn[i];
        ((e.fn[i] = t.jQueryInterface),
        (e.fn[i].Constructor = t),
        (e.fn[i].noConflict = () => ((e.fn[i] = n), t.jQueryInterface)));
      }
    }),
    document.readyState === 'loading' ?
      (Qt.length ||
              document.addEventListener('DOMContentLoaded', () => {
                for (const t of Qt) {
                  t();
                }
              }),
      Qt.push(e)) :
      e());
  };
  const Ut = (t, e = [], i = t) => (typeof t === 'function' ? t.call(...e) : i);
  const Gt = (t, e, i = !0) => {
    if (!i) {
      return void Ut(t);
    }
    const n =
        (t => {
          if (!t) {
            return 0;
          }
          let { transitionDuration: e, transitionDelay: i } =
            window.getComputedStyle(t);
          const n = Number.parseFloat(e);
          const s = Number.parseFloat(i);
          return n || s ?
            ((e = e.split(',')[0]),
            (i = i.split(',')[0]),
            1e3 * (Number.parseFloat(e) + Number.parseFloat(i))) :
            0;
        })(e) + 5;
    let s = !1;
    const o = ({ target: i }) => {
      i === e && ((s = !0), e.removeEventListener(Pt, o), Ut(t));
    };
    (e.addEventListener(Pt, o),
    setTimeout(() => {
      s || Ft(e);
    }, n));
  };
  const Jt = (t, e, i, n) => {
    const s = t.length;
    let o = t.indexOf(e);
    return o === -1 ?
      !i && n ?
        t[s - 1] :
        t[0] :
      ((o += i ? 1 : -1),
      n && (o = (o + s) % s),
      t[Math.max(0, Math.min(o, s - 1))]);
  };
  const Zt = /[^.]*(?=\..*)\.|.*/;
  const te = /\..*/;
  const ee = /::\d+$/;
  const ie = {};
  let ne = 1;
  const se = { mouseenter: 'mouseover', mouseleave: 'mouseout' };
  const oe = new Set([
    'click',
    'dblclick',
    'mouseup',
    'mousedown',
    'contextmenu',
    'mousewheel',
    'DOMMouseScroll',
    'mouseover',
    'mouseout',
    'mousemove',
    'selectstart',
    'selectend',
    'keydown',
    'keypress',
    'keyup',
    'orientationchange',
    'touchstart',
    'touchmove',
    'touchend',
    'touchcancel',
    'pointerdown',
    'pointermove',
    'pointerup',
    'pointerleave',
    'pointercancel',
    'gesturestart',
    'gesturechange',
    'gestureend',
    'focus',
    'blur',
    'change',
    'reset',
    'select',
    'submit',
    'focusin',
    'focusout',
    'load',
    'unload',
    'beforeunload',
    'resize',
    'move',
    'DOMContentLoaded',
    'readystatechange',
    'error',
    'abort',
    'scroll',
  ]);
  function re(t, e) {
    return (e && `${e}::${ne++}`) || t.uidEvent || ne++;
  }
  function ae(t) {
    const e = re(t);
    return ((t.uidEvent = e), (ie[e] = ie[e] || {}), ie[e]);
  }
  function le(t, e, i = null) {
    return Object.values(t).find(
      t => t.callable === e && t.delegationSelector === i,
    );
  }
  function ce(t, e, i) {
    const n = typeof e === 'string';
    const s = n ? i : e || i;
    let o = fe(t);
    return (oe.has(o) || (o = t), [n, s, o]);
  }
  function he(t, e, i, n, s) {
    if (typeof e !== 'string' || !t) {
      return;
    }
    let [o, r, a] = ce(e, i, n);
    if (e in se) {
      const t = t =>
        function(e) {
          if (
            !e.relatedTarget ||
            (e.relatedTarget !== e.delegateTarget &&
              !e.delegateTarget.contains(e.relatedTarget))
          ) {
            return t.call(this, e);
          }
        };
      r = t(r);
    }
    const l = ae(t);
    const c = l[a] || (l[a] = {});
    const h = le(c, r, o ? i : null);
    if (h) {
      return void (h.oneOff = h.oneOff && s);
    }
    const u = re(r, e.replace(Zt, ''));
    const d = o ?
      (function(t, e, i) {
        return function n(s) {
          const o = t.querySelectorAll(e);
          for (let { target: r } = s; r && r !== this; r = r.parentNode) {
            for (const a of o) {
              if (a === r) {
                return (
                  me(s, { delegateTarget: r }),
                  n.oneOff && pe.off(t, s.type, e, i),
                  i.apply(r, [s])
                );
              }
            }
          }
        };
      })(t, i, r) :
      (function(t, e) {
        return function i(n) {
          return (
            me(n, { delegateTarget: t }),
            i.oneOff && pe.off(t, n.type, e),
            e.apply(t, [n])
          );
        };
      })(t, r);
    ((d.delegationSelector = o ? i : null),
    (d.callable = r),
    (d.oneOff = s),
    (d.uidEvent = u),
    (c[u] = d),
    t.addEventListener(a, d, o));
  }
  function ue(t, e, i, n, s) {
    const o = le(e[i], n, s);
    o && (t.removeEventListener(i, o, Boolean(s)), delete e[i][o.uidEvent]);
  }
  function de(t, e, i, n) {
    const s = e[i] || {};
    for (const [o, r] of Object.entries(s)) {
      o.includes(n) && ue(t, e, i, r.callable, r.delegationSelector);
    }
  }
  function fe(t) {
    return ((t = t.replace(te, '')), se[t] || t);
  }
  const pe = {
    on(t, e, i, n) {
      he(t, e, i, n, !1);
    },
    one(t, e, i, n) {
      he(t, e, i, n, !0);
    },
    off(t, e, i, n) {
      if (typeof e !== 'string' || !t) {
        return;
      }
      const [s, o, r] = ce(e, i, n);
      const a = r !== e;
      const l = ae(t);
      const c = l[r] || {};
      const h = e.startsWith('.');
      if (void 0 === o) {
        if (h) {
          for (const i of Object.keys(l)) {
            de(t, l, i, e.slice(1));
          }
        }
        for (const [i, n] of Object.entries(c)) {
          const s = i.replace(ee, '');
          (a && !e.includes(s)) ||
            ue(t, l, r, n.callable, n.delegationSelector);
        }
      } else {
        if (!Object.keys(c).length) {
          return;
        }
        ue(t, l, r, o, s ? i : null);
      }
    },
    trigger(t, e, i) {
      if (typeof e !== 'string' || !t) {
        return null;
      }
      const n = Kt();
      let s = null;
      let o = !0;
      let r = !0;
      let a = !1;
      e !== fe(e) &&
        n &&
        ((s = n.Event(e, i)),
        n(t).trigger(s),
        (o = !s.isPropagationStopped()),
        (r = !s.isImmediatePropagationStopped()),
        (a = s.isDefaultPrevented()));
      const l = me(new Event(e, { bubbles: o, cancelable: !0 }), i);
      return (
        a && l.preventDefault(),
        r && t.dispatchEvent(l),
        l.defaultPrevented && s && s.preventDefault(),
        l
      );
    },
  };
  function me(t, e = {}) {
    for (const [i, n] of Object.entries(e)) {
      try {
        t[i] = n;
      } catch (e) {
        Object.defineProperty(t, i, { configurable: !0, get: () => n });
      }
    }
    return t;
  }
  function ge(t) {
    if (t === 'true') {
      return !0;
    }
    if (t === 'false') {
      return !1;
    }
    if (t === Number(t).toString()) {
      return Number(t);
    }
    if (t === '' || t === 'null') {
      return null;
    }
    if (typeof t !== 'string') {
      return t;
    }
    try {
      return JSON.parse(decodeURIComponent(t));
    } catch (e) {
      return t;
    }
  }
  function _e(t) {
    return t.replace(/[A-Z]/g, t => `-${t.toLowerCase()}`);
  }
  const be = {
    setDataAttribute(t, e, i) {
      t.setAttribute(`data-bs-${_e(e)}`, i);
    },
    removeDataAttribute(t, e) {
      t.removeAttribute(`data-bs-${_e(e)}`);
    },
    getDataAttributes(t) {
      if (!t) {
        return {};
      }
      const e = {};
      const i = Object.keys(t.dataset).filter(
        t => t.startsWith('bs') && !t.startsWith('bsConfig'),
      );
      for (const n of i) {
        let i = n.replace(/^bs/, '');
        ((i = i.charAt(0).toLowerCase() + i.slice(1)),
        (e[i] = ge(t.dataset[n])));
      }
      return e;
    },
    getDataAttribute: (t, e) => ge(t.getAttribute(`data-bs-${_e(e)}`)),
  };
  class ve {
    static get Default() {
      return {};
    }

    static get DefaultType() {
      return {};
    }

    static get NAME() {
      throw new Error(
        'You have to implement the static method "NAME", for each component!',
      );
    }

    _getConfig(t) {
      return (
        (t = this._mergeConfigObj(t)),
        (t = this._configAfterMerge(t)),
        this._typeCheckConfig(t),
        t
      );
    }

    _configAfterMerge(t) {
      return t;
    }

    _mergeConfigObj(t, e) {
      const i = Ht(e) ? be.getDataAttribute(e, 'config') : {};
      return {
        ...this.constructor.Default,
        ...(typeof i === 'object' ? i : {}),
        ...(Ht(e) ? be.getDataAttributes(e) : {}),
        ...(typeof t === 'object' ? t : {}),
      };
    }

    _typeCheckConfig(t, e = this.constructor.DefaultType) {
      for (const [i, n] of Object.entries(e)) {
        const e = t[i];
        const s = Ht(e) ? 'element' : jt(e);
        if (!new RegExp(n).test(s)) {
          throw new TypeError(
            `${this.constructor.NAME.toUpperCase()}: Option "${i}" provided type "${s}" but expected type "${n}".`,
          );
        }
      }
    }
  }
  class ye extends ve {
    constructor(t, e) {
      (super(),
      (t = Wt(t)) &&
          ((this._element = t),
          (this._config = this._getConfig(e)),
          Nt.set(this._element, this.constructor.DATA_KEY, this)));
    }

    dispose() {
      (Nt.remove(this._element, this.constructor.DATA_KEY),
      pe.off(this._element, this.constructor.EVENT_KEY));
      for (const t of Object.getOwnPropertyNames(this)) {
        this[t] = null;
      }
    }

    _queueCallback(t, e, i = !0) {
      Gt(t, e, i);
    }

    _getConfig(t) {
      return (
        (t = this._mergeConfigObj(t, this._element)),
        (t = this._configAfterMerge(t)),
        this._typeCheckConfig(t),
        t
      );
    }

    static getInstance(t) {
      return Nt.get(Wt(t), this.DATA_KEY);
    }

    static getOrCreateInstance(t, e = {}) {
      return (
        this.getInstance(t) || new this(t, typeof e === 'object' ? e : null)
      );
    }

    static get VERSION() {
      return '5.3.8';
    }

    static get DATA_KEY() {
      return `bs.${this.NAME}`;
    }

    static get EVENT_KEY() {
      return `.${this.DATA_KEY}`;
    }

    static eventName(t) {
      return `${t}${this.EVENT_KEY}`;
    }
  }
  const we = t => {
    let e = t.getAttribute('data-bs-target');
    if (!e || e === '#') {
      let i = t.getAttribute('href');
      if (!i || (!i.includes('#') && !i.startsWith('.'))) {
        return null;
      }
      (i.includes('#') && !i.startsWith('#') && (i = `#${i.split('#')[1]}`),
      (e = i && i !== '#' ? i.trim() : null));
    }
    return e ?
      e
        .split(',')
        .map(t => Mt(t))
        .join(',') :
      null;
  };
  const Ae = {
    find: (t, e = document.documentElement) =>
      [].concat(...Element.prototype.querySelectorAll.call(e, t)),
    findOne: (t, e = document.documentElement) =>
      Element.prototype.querySelector.call(e, t),
    children: (t, e) => [].concat(...t.children).filter(t => t.matches(e)),
    parents(t, e) {
      const i = [];
      let n = t.parentNode.closest(e);
      for (; n;) {
        (i.push(n), (n = n.parentNode.closest(e)));
      }
      return i;
    },
    prev(t, e) {
      let i = t.previousElementSibling;
      for (; i;) {
        if (i.matches(e)) {
          return [i];
        }
        i = i.previousElementSibling;
      }
      return [];
    },
    next(t, e) {
      let i = t.nextElementSibling;
      for (; i;) {
        if (i.matches(e)) {
          return [i];
        }
        i = i.nextElementSibling;
      }
      return [];
    },
    focusableChildren(t) {
      const e = [
        'a',
        'button',
        'input',
        'textarea',
        'select',
        'details',
        '[tabindex]',
        '[contenteditable="true"]',
      ]
        .map(t => `${t}:not([tabindex^="-"])`)
        .join(',');
      return this.find(e, t).filter(t => !zt(t) && Bt(t));
    },
    getSelectorFromElement(t) {
      const e = we(t);
      return e && Ae.findOne(e) ? e : null;
    },
    getElementFromSelector(t) {
      const e = we(t);
      return e ? Ae.findOne(e) : null;
    },
    getMultipleElementsFromSelector(t) {
      const e = we(t);
      return e ? Ae.find(e) : [];
    },
  };
  const Ee = (t, e = 'hide') => {
    const i = `click.dismiss${t.EVENT_KEY}`;
    const n = t.NAME;
    pe.on(document, i, `[data-bs-dismiss="${n}"]`, function(i) {
      if (
        (['A', 'AREA'].includes(this.tagName) && i.preventDefault(), zt(this))
      ) {
        return;
      }
      const s = Ae.getElementFromSelector(this) || this.closest(`.${n}`);
      t.getOrCreateInstance(s)[e]();
    });
  };
  const Ce = '.bs.alert';
  const Te = `close${Ce}`;
  const Oe = `closed${Ce}`;
  class xe extends ye {
    static get NAME() {
      return 'alert';
    }

    close() {
      if (pe.trigger(this._element, Te).defaultPrevented) {
        return;
      }
      this._element.classList.remove('show');
      const t = this._element.classList.contains('fade');
      this._queueCallback(() => this._destroyElement(), this._element, t);
    }

    _destroyElement() {
      (this._element.remove(), pe.trigger(this._element, Oe), this.dispose());
    }

    static jQueryInterface(t) {
      return this.each(function() {
        const e = xe.getOrCreateInstance(this);
        if (typeof t === 'string') {
          if (void 0 === e[t] || t.startsWith('_') || t === 'constructor') {
            throw new TypeError(`No method named "${t}"`);
          }
          e[t](this);
        }
      });
    }
  }
  (Ee(xe, 'close'), Yt(xe));
  const ke = '[data-bs-toggle="button"]';
  class Le extends ye {
    static get NAME() {
      return 'button';
    }

    toggle() {
      this._element.setAttribute(
        'aria-pressed',
        this._element.classList.toggle('active'),
      );
    }

    static jQueryInterface(t) {
      return this.each(function() {
        const e = Le.getOrCreateInstance(this);
        t === 'toggle' && e[t]();
      });
    }
  }
  (pe.on(document, 'click.bs.button.data-api', ke, t => {
    t.preventDefault();
    const e = t.target.closest(ke);
    Le.getOrCreateInstance(e).toggle();
  }),
  Yt(Le));
  const Se = '.bs.swipe';
  const De = `touchstart${Se}`;
  const $e = `touchmove${Se}`;
  const Ie = `touchend${Se}`;
  const Ne = `pointerdown${Se}`;
  const Pe = `pointerup${Se}`;
  const Me = { endCallback: null, leftCallback: null, rightCallback: null };
  const je = {
    endCallback: '(function|null)',
    leftCallback: '(function|null)',
    rightCallback: '(function|null)',
  };
  class Fe extends ve {
    constructor(t, e) {
      (super(),
      (this._element = t),
      t &&
          Fe.isSupported() &&
          ((this._config = this._getConfig(e)),
          (this._deltaX = 0),
          (this._supportPointerEvents = Boolean(window.PointerEvent)),
          this._initEvents()));
    }

    static get Default() {
      return Me;
    }

    static get DefaultType() {
      return je;
    }

    static get NAME() {
      return 'swipe';
    }

    dispose() {
      pe.off(this._element, Se);
    }

    _start(t) {
      this._supportPointerEvents ?
        this._eventIsPointerPenTouch(t) && (this._deltaX = t.clientX) :
        (this._deltaX = t.touches[0].clientX);
    }

    _end(t) {
      (this._eventIsPointerPenTouch(t) &&
        (this._deltaX = t.clientX - this._deltaX),
      this._handleSwipe(),
      Ut(this._config.endCallback));
    }

    _move(t) {
      this._deltaX =
        t.touches && t.touches.length > 1 ?
          0 :
          t.touches[0].clientX - this._deltaX;
    }

    _handleSwipe() {
      const t = Math.abs(this._deltaX);
      if (t <= 40) {
        return;
      }
      const e = t / this._deltaX;
      ((this._deltaX = 0),
      e &&
          Ut(e > 0 ? this._config.rightCallback : this._config.leftCallback));
    }

    _initEvents() {
      this._supportPointerEvents ?
        (pe.on(this._element, Ne, t => this._start(t)),
        pe.on(this._element, Pe, t => this._end(t)),
        this._element.classList.add('pointer-event')) :
        (pe.on(this._element, De, t => this._start(t)),
        pe.on(this._element, $e, t => this._move(t)),
        pe.on(this._element, Ie, t => this._end(t)));
    }

    _eventIsPointerPenTouch(t) {
      return (
        this._supportPointerEvents &&
        (t.pointerType === 'pen' || t.pointerType === 'touch')
      );
    }

    static isSupported() {
      return (
        'ontouchstart' in document.documentElement ||
        navigator.maxTouchPoints > 0
      );
    }
  }
  const He = '.bs.carousel';
  const We = '.data-api';
  const Be = 'ArrowLeft';
  const ze = 'ArrowRight';
  const Re = 'next';
  const qe = 'prev';
  const Ve = 'left';
  const Ke = 'right';
  const Qe = `slide${He}`;
  const Xe = `slid${He}`;
  const Ye = `keydown${He}`;
  const Ue = `mouseenter${He}`;
  const Ge = `mouseleave${He}`;
  const Je = `dragstart${He}`;
  const Ze = `load${He}${We}`;
  const ti = `click${He}${We}`;
  const ei = 'carousel';
  const ii = 'active';
  const ni = '.active';
  const si = '.carousel-item';
  const oi = ni + si;
  const ri = { [Be]: Ke, [ze]: Ve };
  const ai = {
    interval: 5e3,
    keyboard: !0,
    pause: 'hover',
    ride: !1,
    touch: !0,
    wrap: !0,
  };
  const li = {
    interval: '(number|boolean)',
    keyboard: 'boolean',
    pause: '(string|boolean)',
    ride: '(boolean|string)',
    touch: 'boolean',
    wrap: 'boolean',
  };
  class ci extends ye {
    constructor(t, e) {
      (super(t, e),
      (this._interval = null),
      (this._activeElement = null),
      (this._isSliding = !1),
      (this.touchTimeout = null),
      (this._swipeHelper = null),
      (this._indicatorsElement = Ae.findOne(
        '.carousel-indicators',
        this._element,
      )),
      this._addEventListeners(),
      this._config.ride === ei && this.cycle());
    }

    static get Default() {
      return ai;
    }

    static get DefaultType() {
      return li;
    }

    static get NAME() {
      return 'carousel';
    }

    next() {
      this._slide(Re);
    }

    nextWhenVisible() {
      !document.hidden && Bt(this._element) && this.next();
    }

    prev() {
      this._slide(qe);
    }

    pause() {
      (this._isSliding && Ft(this._element), this._clearInterval());
    }

    cycle() {
      (this._clearInterval(),
      this._updateInterval(),
      (this._interval = setInterval(
        () => this.nextWhenVisible(),
        this._config.interval,
      )));
    }

    _maybeEnableCycle() {
      this._config.ride &&
        (this._isSliding ?
          pe.one(this._element, Xe, () => this.cycle()) :
          this.cycle());
    }

    to(t) {
      const e = this._getItems();
      if (t > e.length - 1 || t < 0) {
        return;
      }
      if (this._isSliding) {
        return void pe.one(this._element, Xe, () => this.to(t));
      }
      const i = this._getItemIndex(this._getActive());
      if (i === t) {
        return;
      }
      const n = t > i ? Re : qe;
      this._slide(n, e[t]);
    }

    dispose() {
      (this._swipeHelper && this._swipeHelper.dispose(), super.dispose());
    }

    _configAfterMerge(t) {
      return ((t.defaultInterval = t.interval), t);
    }

    _addEventListeners() {
      (this._config.keyboard &&
        pe.on(this._element, Ye, t => this._keydown(t)),
      this._config.pause === 'hover' &&
          (pe.on(this._element, Ue, () => this.pause()),
          pe.on(this._element, Ge, () => this._maybeEnableCycle())),
      this._config.touch &&
          Fe.isSupported() &&
          this._addTouchEventListeners());
    }

    _addTouchEventListeners() {
      for (const t of Ae.find('.carousel-item img', this._element)) {
        pe.on(t, Je, t => t.preventDefault());
      }
      const t = {
        leftCallback: () => this._slide(this._directionToOrder(Ve)),
        rightCallback: () => this._slide(this._directionToOrder(Ke)),
        endCallback: () => {
          this._config.pause === 'hover' &&
            (this.pause(),
            this.touchTimeout && clearTimeout(this.touchTimeout),
            (this.touchTimeout = setTimeout(
              () => this._maybeEnableCycle(),
              500 + this._config.interval,
            )));
        },
      };
      this._swipeHelper = new Fe(this._element, t);
    }

    _keydown(t) {
      if (/input|textarea/i.test(t.target.tagName)) {
        return;
      }
      const e = ri[t.key];
      e && (t.preventDefault(), this._slide(this._directionToOrder(e)));
    }

    _getItemIndex(t) {
      return this._getItems().indexOf(t);
    }

    _setActiveIndicatorElement(t) {
      if (!this._indicatorsElement) {
        return;
      }
      const e = Ae.findOne(ni, this._indicatorsElement);
      (e.classList.remove(ii), e.removeAttribute('aria-current'));
      const i = Ae.findOne(
        `[data-bs-slide-to="${t}"]`,
        this._indicatorsElement,
      );
      i && (i.classList.add(ii), i.setAttribute('aria-current', 'true'));
    }

    _updateInterval() {
      const t = this._activeElement || this._getActive();
      if (!t) {
        return;
      }
      const e = Number.parseInt(t.getAttribute('data-bs-interval'), 10);
      this._config.interval = e || this._config.defaultInterval;
    }

    _slide(t, e = null) {
      if (this._isSliding) {
        return;
      }
      const i = this._getActive();
      const n = t === Re;
      const s = e || Jt(this._getItems(), i, n, this._config.wrap);
      if (s === i) {
        return;
      }
      const o = this._getItemIndex(s);
      const r = e =>
        pe.trigger(this._element, e, {
          relatedTarget: s,
          direction: this._orderToDirection(t),
          from: this._getItemIndex(i),
          to: o,
        });
      if (r(Qe).defaultPrevented) {
        return;
      }
      if (!i || !s) {
        return;
      }
      const a = Boolean(this._interval);
      (this.pause(),
      (this._isSliding = !0),
      this._setActiveIndicatorElement(o),
      (this._activeElement = s));
      const l = n ? 'carousel-item-start' : 'carousel-item-end';
      const c = n ? 'carousel-item-next' : 'carousel-item-prev';
      (s.classList.add(c), Vt(s), i.classList.add(l), s.classList.add(l));
      (this._queueCallback(
        () => {
          (s.classList.remove(l, c),
          s.classList.add(ii),
          i.classList.remove(ii, c, l),
          (this._isSliding = !1),
          r(Xe));
        },
        i,
        this._isAnimated(),
      ),
      a && this.cycle());
    }

    _isAnimated() {
      return this._element.classList.contains('slide');
    }

    _getActive() {
      return Ae.findOne(oi, this._element);
    }

    _getItems() {
      return Ae.find(si, this._element);
    }

    _clearInterval() {
      this._interval &&
        (clearInterval(this._interval), (this._interval = null));
    }

    _directionToOrder(t) {
      return Xt() ? (t === Ve ? qe : Re) : t === Ve ? Re : qe;
    }

    _orderToDirection(t) {
      return Xt() ? (t === qe ? Ve : Ke) : t === qe ? Ke : Ve;
    }

    static jQueryInterface(t) {
      return this.each(function() {
        const e = ci.getOrCreateInstance(this, t);
        if (typeof t !== 'number') {
          if (typeof t === 'string') {
            if (void 0 === e[t] || t.startsWith('_') || t === 'constructor') {
              throw new TypeError(`No method named "${t}"`);
            }
            e[t]();
          }
        } else {
          e.to(t);
        }
      });
    }
  }
  (pe.on(document, ti, '[data-bs-slide], [data-bs-slide-to]', function(t) {
    const e = Ae.getElementFromSelector(this);
    if (!e || !e.classList.contains(ei)) {
      return;
    }
    t.preventDefault();
    const i = ci.getOrCreateInstance(e);
    const n = this.getAttribute('data-bs-slide-to');
    return n ?
      (i.to(n), void i._maybeEnableCycle()) :
      be.getDataAttribute(this, 'slide') === 'next' ?
        (i.next(), void i._maybeEnableCycle()) :
        (i.prev(), void i._maybeEnableCycle());
  }),
  pe.on(window, Ze, () => {
    const t = Ae.find('[data-bs-ride="carousel"]');
    for (const e of t) {
      ci.getOrCreateInstance(e);
    }
  }),
  Yt(ci));
  const hi = '.bs.collapse';
  const ui = `show${hi}`;
  const di = `shown${hi}`;
  const fi = `hide${hi}`;
  const pi = `hidden${hi}`;
  const mi = `click${hi}.data-api`;
  const gi = 'show';
  const _i = 'collapse';
  const bi = 'collapsing';
  const vi = `:scope .${_i} .${_i}`;
  const yi = '[data-bs-toggle="collapse"]';
  const wi = { parent: null, toggle: !0 };
  const Ai = { parent: '(null|element)', toggle: 'boolean' };
  class Ei extends ye {
    constructor(t, e) {
      (super(t, e), (this._isTransitioning = !1), (this._triggerArray = []));
      const i = Ae.find(yi);
      for (const t of i) {
        const e = Ae.getSelectorFromElement(t);
        const i = Ae.find(e).filter(t => t === this._element);
        e !== null && i.length && this._triggerArray.push(t);
      }
      (this._initializeChildren(),
      this._config.parent ||
          this._addAriaAndCollapsedClass(this._triggerArray, this._isShown()),
      this._config.toggle && this.toggle());
    }

    static get Default() {
      return wi;
    }

    static get DefaultType() {
      return Ai;
    }

    static get NAME() {
      return 'collapse';
    }

    toggle() {
      this._isShown() ? this.hide() : this.show();
    }

    show() {
      if (this._isTransitioning || this._isShown()) {
        return;
      }
      let t = [];
      if (
        (this._config.parent &&
          (t = this._getFirstLevelChildren(
            '.collapse.show, .collapse.collapsing',
          )
            .filter(t => t !== this._element)
            .map(t => Ei.getOrCreateInstance(t, { toggle: !1 }))),
        t.length && t[0]._isTransitioning)
      ) {
        return;
      }
      if (pe.trigger(this._element, ui).defaultPrevented) {
        return;
      }
      for (const e of t) {
        e.hide();
      }
      const e = this._getDimension();
      (this._element.classList.remove(_i),
      this._element.classList.add(bi),
      (this._element.style[e] = 0),
      this._addAriaAndCollapsedClass(this._triggerArray, !0),
      (this._isTransitioning = !0));
      const i = `scroll${e[0].toUpperCase() + e.slice(1)}`;
      (this._queueCallback(
        () => {
          ((this._isTransitioning = !1),
          this._element.classList.remove(bi),
          this._element.classList.add(_i, gi),
          (this._element.style[e] = ''),
          pe.trigger(this._element, di));
        },
        this._element,
        !0,
      ),
      (this._element.style[e] = `${this._element[i]}px`));
    }

    hide() {
      if (this._isTransitioning || !this._isShown()) {
        return;
      }
      if (pe.trigger(this._element, fi).defaultPrevented) {
        return;
      }
      const t = this._getDimension();
      ((this._element.style[t] =
        `${this._element.getBoundingClientRect()[t]}px`),
      Vt(this._element),
      this._element.classList.add(bi),
      this._element.classList.remove(_i, gi));
      for (const t of this._triggerArray) {
        const e = Ae.getElementFromSelector(t);
        e && !this._isShown(e) && this._addAriaAndCollapsedClass([t], !1);
      }
      this._isTransitioning = !0;
      ((this._element.style[t] = ''),
      this._queueCallback(
        () => {
          ((this._isTransitioning = !1),
          this._element.classList.remove(bi),
          this._element.classList.add(_i),
          pe.trigger(this._element, pi));
        },
        this._element,
        !0,
      ));
    }

    _isShown(t = this._element) {
      return t.classList.contains(gi);
    }

    _configAfterMerge(t) {
      return ((t.toggle = Boolean(t.toggle)), (t.parent = Wt(t.parent)), t);
    }

    _getDimension() {
      return this._element.classList.contains('collapse-horizontal') ?
        'width' :
        'height';
    }

    _initializeChildren() {
      if (!this._config.parent) {
        return;
      }
      const t = this._getFirstLevelChildren(yi);
      for (const e of t) {
        const t = Ae.getElementFromSelector(e);
        t && this._addAriaAndCollapsedClass([e], this._isShown(t));
      }
    }

    _getFirstLevelChildren(t) {
      const e = Ae.find(vi, this._config.parent);
      return Ae.find(t, this._config.parent).filter(t => !e.includes(t));
    }

    _addAriaAndCollapsedClass(t, e) {
      if (t.length) {
        for (const i of t) {
          (i.classList.toggle('collapsed', !e),
          i.setAttribute('aria-expanded', e));
        }
      }
    }

    static jQueryInterface(t) {
      const e = {};
      return (
        typeof t === 'string' && /show|hide/.test(t) && (e.toggle = !1),
        this.each(function() {
          const i = Ei.getOrCreateInstance(this, e);
          if (typeof t === 'string') {
            if (void 0 === i[t]) {
              throw new TypeError(`No method named "${t}"`);
            }
            i[t]();
          }
        })
      );
    }
  }
  (pe.on(document, mi, yi, function(t) {
    (t.target.tagName === 'A' ||
      (t.delegateTarget && t.delegateTarget.tagName === 'A')) &&
      t.preventDefault();
    for (const t of Ae.getMultipleElementsFromSelector(this)) {
      Ei.getOrCreateInstance(t, { toggle: !1 }).toggle();
    }
  }),
  Yt(Ei));
  const Ci = 'dropdown';
  const Ti = '.bs.dropdown';
  const Oi = '.data-api';
  const xi = 'ArrowUp';
  const ki = 'ArrowDown';
  const Li = `hide${Ti}`;
  const Si = `hidden${Ti}`;
  const Di = `show${Ti}`;
  const $i = `shown${Ti}`;
  const Ii = `click${Ti}${Oi}`;
  const Ni = `keydown${Ti}${Oi}`;
  const Pi = `keyup${Ti}${Oi}`;
  const Mi = 'show';
  const ji = '[data-bs-toggle="dropdown"]:not(.disabled):not(:disabled)';
  const Fi = `${ji}.${Mi}`;
  const Hi = '.dropdown-menu';
  const Wi = Xt() ? 'top-end' : 'top-start';
  const Bi = Xt() ? 'top-start' : 'top-end';
  const zi = Xt() ? 'bottom-end' : 'bottom-start';
  const Ri = Xt() ? 'bottom-start' : 'bottom-end';
  const qi = Xt() ? 'left-start' : 'right-start';
  const Vi = Xt() ? 'right-start' : 'left-start';
  const Ki = {
    autoClose: !0,
    boundary: 'clippingParents',
    display: 'dynamic',
    offset: [0, 2],
    popperConfig: null,
    reference: 'toggle',
  };
  const Qi = {
    autoClose: '(boolean|string)',
    boundary: '(string|element)',
    display: 'string',
    offset: '(array|string|function)',
    popperConfig: '(null|object|function)',
    reference: '(string|element|object)',
  };
  class Xi extends ye {
    constructor(t, e) {
      (super(t, e),
      (this._popper = null),
      (this._parent = this._element.parentNode),
      (this._menu =
          Ae.next(this._element, Hi)[0] ||
          Ae.prev(this._element, Hi)[0] ||
          Ae.findOne(Hi, this._parent)),
      (this._inNavbar = this._detectNavbar()));
    }

    static get Default() {
      return Ki;
    }

    static get DefaultType() {
      return Qi;
    }

    static get NAME() {
      return Ci;
    }

    toggle() {
      return this._isShown() ? this.hide() : this.show();
    }

    show() {
      if (zt(this._element) || this._isShown()) {
        return;
      }
      const t = { relatedTarget: this._element };
      if (!pe.trigger(this._element, Di, t).defaultPrevented) {
        if (
          (this._createPopper(),
          'ontouchstart' in document.documentElement &&
            !this._parent.closest('.navbar-nav'))
        ) {
          for (const t of [].concat(...document.body.children)) {
            pe.on(t, 'mouseover', qt);
          }
        }
        (this._element.focus(),
        this._element.setAttribute('aria-expanded', !0),
        this._menu.classList.add(Mi),
        this._element.classList.add(Mi),
        pe.trigger(this._element, $i, t));
      }
    }

    hide() {
      if (zt(this._element) || !this._isShown()) {
        return;
      }
      const t = { relatedTarget: this._element };
      this._completeHide(t);
    }

    dispose() {
      (this._popper && this._popper.destroy(), super.dispose());
    }

    update() {
      ((this._inNavbar = this._detectNavbar()),
      this._popper && this._popper.update());
    }

    _completeHide(t) {
      if (!pe.trigger(this._element, Li, t).defaultPrevented) {
        if ('ontouchstart' in document.documentElement) {
          for (const t of [].concat(...document.body.children)) {
            pe.off(t, 'mouseover', qt);
          }
        }
        (this._popper && this._popper.destroy(),
        this._menu.classList.remove(Mi),
        this._element.classList.remove(Mi),
        this._element.setAttribute('aria-expanded', 'false'),
        be.removeDataAttribute(this._menu, 'popper'),
        pe.trigger(this._element, Si, t));
      }
    }

    _getConfig(t) {
      if (
        typeof (t = super._getConfig(t)).reference === 'object' &&
        !Ht(t.reference) &&
        typeof t.reference.getBoundingClientRect !== 'function'
      ) {
        throw new TypeError(
          `${Ci.toUpperCase()}: Option "reference" provided type "object" without a required "getBoundingClientRect" method.`,
        );
      }
      return t;
    }

    _createPopper() {
      let t = this._element;
      this._config.reference === 'parent' ?
        (t = this._parent) :
        Ht(this._config.reference) ?
          (t = Wt(this._config.reference)) :
          typeof this._config.reference === 'object' &&
            (t = this._config.reference);
      const e = this._getPopperConfig();
      this._popper = Dt(t, this._menu, e);
    }

    _isShown() {
      return this._menu.classList.contains(Mi);
    }

    _getPlacement() {
      const t = this._parent;
      if (t.classList.contains('dropend')) {
        return qi;
      }
      if (t.classList.contains('dropstart')) {
        return Vi;
      }
      if (t.classList.contains('dropup-center')) {
        return 'top';
      }
      if (t.classList.contains('dropdown-center')) {
        return 'bottom';
      }
      const e =
        getComputedStyle(this._menu).getPropertyValue('--bs-position').trim() ===
        'end';
      return t.classList.contains('dropup') ? (e ? Bi : Wi) : e ? Ri : zi;
    }

    _detectNavbar() {
      return this._element.closest('.navbar') !== null;
    }

    _getOffset() {
      const { offset: t } = this._config;
      return typeof t === 'string' ?
        t.split(',').map(t => Number.parseInt(t, 10)) :
        typeof t === 'function' ?
          e => t(e, this._element) :
          t;
    }

    _getPopperConfig() {
      const t = {
        placement: this._getPlacement(),
        modifiers: [
          {
            name: 'preventOverflow',
            options: { boundary: this._config.boundary },
          },
          { name: 'offset', options: { offset: this._getOffset() } },
        ],
      };
      return (
        (this._inNavbar || this._config.display === 'static') &&
          (be.setDataAttribute(this._menu, 'popper', 'static'),
          (t.modifiers = [{ name: 'applyStyles', enabled: !1 }])),
        { ...t, ...Ut(this._config.popperConfig, [void 0, t]) }
      );
    }

    _selectMenuItem({ key: t, target: e }) {
      const i = Ae.find(
        '.dropdown-menu .dropdown-item:not(.disabled):not(:disabled)',
        this._menu,
      ).filter(t => Bt(t));
      i.length && Jt(i, e, t === ki, !i.includes(e)).focus();
    }

    static jQueryInterface(t) {
      return this.each(function() {
        const e = Xi.getOrCreateInstance(this, t);
        if (typeof t === 'string') {
          if (void 0 === e[t]) {
            throw new TypeError(`No method named "${t}"`);
          }
          e[t]();
        }
      });
    }

    static clearMenus(t) {
      if (t.button === 2 || (t.type === 'keyup' && t.key !== 'Tab')) {
        return;
      }
      const e = Ae.find(Fi);
      for (const i of e) {
        const e = Xi.getInstance(i);
        if (!e || !1 === e._config.autoClose) {
          continue;
        }
        const n = t.composedPath();
        const s = n.includes(e._menu);
        if (
          n.includes(e._element) ||
          (e._config.autoClose === 'inside' && !s) ||
          (e._config.autoClose === 'outside' && s)
        ) {
          continue;
        }
        if (
          e._menu.contains(t.target) &&
          ((t.type === 'keyup' && t.key === 'Tab') ||
            /input|select|option|textarea|form/i.test(t.target.tagName))
        ) {
          continue;
        }
        const o = { relatedTarget: e._element };
        (t.type === 'click' && (o.clickEvent = t), e._completeHide(o));
      }
    }

    static dataApiKeydownHandler(t) {
      const e = /input|textarea/i.test(t.target.tagName);
      const i = t.key === 'Escape';
      const n = [xi, ki].includes(t.key);
      if (!n && !i) {
        return;
      }
      if (e && !i) {
        return;
      }
      t.preventDefault();
      const s = this.matches(ji) ?
        this :
        Ae.prev(this, ji)[0] ||
            Ae.next(this, ji)[0] ||
            Ae.findOne(ji, t.delegateTarget.parentNode);
      const o = Xi.getOrCreateInstance(s);
      if (n) {
        return (t.stopPropagation(), o.show(), void o._selectMenuItem(t));
      }
      o._isShown() && (t.stopPropagation(), o.hide(), s.focus());
    }
  }
  (pe.on(document, Ni, ji, Xi.dataApiKeydownHandler),
  pe.on(document, Ni, Hi, Xi.dataApiKeydownHandler),
  pe.on(document, Ii, Xi.clearMenus),
  pe.on(document, Pi, Xi.clearMenus),
  pe.on(document, Ii, ji, function(t) {
    (t.preventDefault(), Xi.getOrCreateInstance(this).toggle());
  }),
  Yt(Xi));
  const Yi = 'backdrop';
  const Ui = 'show';
  const Gi = `mousedown.bs.${Yi}`;
  const Ji = {
    className: 'modal-backdrop',
    clickCallback: null,
    isAnimated: !1,
    isVisible: !0,
    rootElement: 'body',
  };
  const Zi = {
    className: 'string',
    clickCallback: '(function|null)',
    isAnimated: 'boolean',
    isVisible: 'boolean',
    rootElement: '(element|string)',
  };
  class tn extends ve {
    constructor(t) {
      (super(),
      (this._config = this._getConfig(t)),
      (this._isAppended = !1),
      (this._element = null));
    }

    static get Default() {
      return Ji;
    }

    static get DefaultType() {
      return Zi;
    }

    static get NAME() {
      return Yi;
    }

    show(t) {
      if (!this._config.isVisible) {
        return void Ut(t);
      }
      this._append();
      const e = this._getElement();
      (this._config.isAnimated && Vt(e),
      e.classList.add(Ui),
      this._emulateAnimation(() => {
        Ut(t);
      }));
    }

    hide(t) {
      this._config.isVisible ?
        (this._getElement().classList.remove(Ui),
        this._emulateAnimation(() => {
          (this.dispose(), Ut(t));
        })) :
        Ut(t);
    }

    dispose() {
      this._isAppended &&
        (pe.off(this._element, Gi),
        this._element.remove(),
        (this._isAppended = !1));
    }

    _getElement() {
      if (!this._element) {
        const t = document.createElement('div');
        ((t.className = this._config.className),
        this._config.isAnimated && t.classList.add('fade'),
        (this._element = t));
      }
      return this._element;
    }

    _configAfterMerge(t) {
      return ((t.rootElement = Wt(t.rootElement)), t);
    }

    _append() {
      if (this._isAppended) {
        return;
      }
      const t = this._getElement();
      (this._config.rootElement.append(t),
      pe.on(t, Gi, () => {
        Ut(this._config.clickCallback);
      }),
      (this._isAppended = !0));
    }

    _emulateAnimation(t) {
      Gt(t, this._getElement(), this._config.isAnimated);
    }
  }
  const en = '.bs.focustrap';
  const nn = `focusin${en}`;
  const sn = `keydown.tab${en}`;
  const on = 'backward';
  const rn = { autofocus: !0, trapElement: null };
  const an = { autofocus: 'boolean', trapElement: 'element' };
  class ln extends ve {
    constructor(t) {
      (super(),
      (this._config = this._getConfig(t)),
      (this._isActive = !1),
      (this._lastTabNavDirection = null));
    }

    static get Default() {
      return rn;
    }

    static get DefaultType() {
      return an;
    }

    static get NAME() {
      return 'focustrap';
    }

    activate() {
      this._isActive ||
        (this._config.autofocus && this._config.trapElement.focus(),
        pe.off(document, en),
        pe.on(document, nn, t => this._handleFocusin(t)),
        pe.on(document, sn, t => this._handleKeydown(t)),
        (this._isActive = !0));
    }

    deactivate() {
      this._isActive && ((this._isActive = !1), pe.off(document, en));
    }

    _handleFocusin(t) {
      const { trapElement: e } = this._config;
      if (t.target === document || t.target === e || e.contains(t.target)) {
        return;
      }
      const i = Ae.focusableChildren(e);
      i.length === 0 ?
        e.focus() :
        this._lastTabNavDirection === on ?
          i[i.length - 1].focus() :
          i[0].focus();
    }

    _handleKeydown(t) {
      t.key === 'Tab' &&
        (this._lastTabNavDirection = t.shiftKey ? on : 'forward');
    }
  }
  const cn = '.fixed-top, .fixed-bottom, .is-fixed, .sticky-top';
  const hn = '.sticky-top';
  const un = 'padding-right';
  const dn = 'margin-right';
  class fn {
    constructor() {
      this._element = document.body;
    }

    getWidth() {
      const t = document.documentElement.clientWidth;
      return Math.abs(window.innerWidth - t);
    }

    hide() {
      const t = this.getWidth();
      (this._disableOverFlow(),
      this._setElementAttributes(this._element, un, e => e + t),
      this._setElementAttributes(cn, un, e => e + t),
      this._setElementAttributes(hn, dn, e => e - t));
    }

    reset() {
      (this._resetElementAttributes(this._element, 'overflow'),
      this._resetElementAttributes(this._element, un),
      this._resetElementAttributes(cn, un),
      this._resetElementAttributes(hn, dn));
    }

    isOverflowing() {
      return this.getWidth() > 0;
    }

    _disableOverFlow() {
      (this._saveInitialAttribute(this._element, 'overflow'),
      (this._element.style.overflow = 'hidden'));
    }

    _setElementAttributes(t, e, i) {
      const n = this.getWidth();
      this._applyManipulationCallback(t, t => {
        if (t !== this._element && window.innerWidth > t.clientWidth + n) {
          return;
        }
        this._saveInitialAttribute(t, e);
        const s = window.getComputedStyle(t).getPropertyValue(e);
        t.style.setProperty(e, `${i(Number.parseFloat(s))}px`);
      });
    }

    _saveInitialAttribute(t, e) {
      const i = t.style.getPropertyValue(e);
      i && be.setDataAttribute(t, e, i);
    }

    _resetElementAttributes(t, e) {
      this._applyManipulationCallback(t, t => {
        const i = be.getDataAttribute(t, e);
        i !== null ?
          (be.removeDataAttribute(t, e), t.style.setProperty(e, i)) :
          t.style.removeProperty(e);
      });
    }

    _applyManipulationCallback(t, e) {
      if (Ht(t)) {
        e(t);
      } else {
        for (const i of Ae.find(t, this._element)) {
          e(i);
        }
      }
    }
  }
  const pn = '.bs.modal';
  const mn = `hide${pn}`;
  const gn = `hidePrevented${pn}`;
  const _n = `hidden${pn}`;
  const bn = `show${pn}`;
  const vn = `shown${pn}`;
  const yn = `resize${pn}`;
  const wn = `click.dismiss${pn}`;
  const An = `mousedown.dismiss${pn}`;
  const En = `keydown.dismiss${pn}`;
  const Cn = `click${pn}.data-api`;
  const Tn = 'modal-open';
  const On = 'show';
  const xn = 'modal-static';
  const kn = { backdrop: !0, focus: !0, keyboard: !0 };
  const Ln = {
    backdrop: '(boolean|string)',
    focus: 'boolean',
    keyboard: 'boolean',
  };
  class Sn extends ye {
    constructor(t, e) {
      (super(t, e),
      (this._dialog = Ae.findOne('.modal-dialog', this._element)),
      (this._backdrop = this._initializeBackDrop()),
      (this._focustrap = this._initializeFocusTrap()),
      (this._isShown = !1),
      (this._isTransitioning = !1),
      (this._scrollBar = new fn()),
      this._addEventListeners());
    }

    static get Default() {
      return kn;
    }

    static get DefaultType() {
      return Ln;
    }

    static get NAME() {
      return 'modal';
    }

    toggle(t) {
      return this._isShown ? this.hide() : this.show(t);
    }

    show(t) {
      if (this._isShown || this._isTransitioning) {
        return;
      }
      pe.trigger(this._element, bn, { relatedTarget: t }).defaultPrevented ||
        ((this._isShown = !0),
        (this._isTransitioning = !0),
        this._scrollBar.hide(),
        document.body.classList.add(Tn),
        this._adjustDialog(),
        this._backdrop.show(() => this._showElement(t)));
    }

    hide() {
      if (!this._isShown || this._isTransitioning) {
        return;
      }
      pe.trigger(this._element, mn).defaultPrevented ||
        ((this._isShown = !1),
        (this._isTransitioning = !0),
        this._focustrap.deactivate(),
        this._element.classList.remove(On),
        this._queueCallback(
          () => this._hideModal(),
          this._element,
          this._isAnimated(),
        ));
    }

    dispose() {
      (pe.off(window, pn),
      pe.off(this._dialog, pn),
      this._backdrop.dispose(),
      this._focustrap.deactivate(),
      super.dispose());
    }

    handleUpdate() {
      this._adjustDialog();
    }

    _initializeBackDrop() {
      return new tn({
        isVisible: Boolean(this._config.backdrop),
        isAnimated: this._isAnimated(),
      });
    }

    _initializeFocusTrap() {
      return new ln({ trapElement: this._element });
    }

    _showElement(t) {
      (document.body.contains(this._element) ||
        document.body.append(this._element),
      (this._element.style.display = 'block'),
      this._element.removeAttribute('aria-hidden'),
      this._element.setAttribute('aria-modal', !0),
      this._element.setAttribute('role', 'dialog'),
      (this._element.scrollTop = 0));
      const e = Ae.findOne('.modal-body', this._dialog);
      (e && (e.scrollTop = 0),
      Vt(this._element),
      this._element.classList.add(On));
      this._queueCallback(
        () => {
          (this._config.focus && this._focustrap.activate(),
          (this._isTransitioning = !1),
          pe.trigger(this._element, vn, { relatedTarget: t }));
        },
        this._dialog,
        this._isAnimated(),
      );
    }

    _addEventListeners() {
      (pe.on(this._element, En, t => {
        t.key === 'Escape' &&
          (this._config.keyboard ?
            this.hide() :
            this._triggerBackdropTransition());
      }),
      pe.on(window, yn, () => {
        this._isShown && !this._isTransitioning && this._adjustDialog();
      }),
      pe.on(this._element, An, t => {
        pe.one(this._element, wn, e => {
          this._element === t.target &&
              this._element === e.target &&
              (this._config.backdrop !== 'static' ?
                this._config.backdrop && this.hide() :
                this._triggerBackdropTransition());
        });
      }));
    }

    _hideModal() {
      ((this._element.style.display = 'none'),
      this._element.setAttribute('aria-hidden', !0),
      this._element.removeAttribute('aria-modal'),
      this._element.removeAttribute('role'),
      (this._isTransitioning = !1),
      this._backdrop.hide(() => {
        (document.body.classList.remove(Tn),
        this._resetAdjustments(),
        this._scrollBar.reset(),
        pe.trigger(this._element, _n));
      }));
    }

    _isAnimated() {
      return this._element.classList.contains('fade');
    }

    _triggerBackdropTransition() {
      if (pe.trigger(this._element, gn).defaultPrevented) {
        return;
      }
      const t =
          this._element.scrollHeight > document.documentElement.clientHeight;
      const e = this._element.style.overflowY;
      e === 'hidden' ||
        this._element.classList.contains(xn) ||
        (t || (this._element.style.overflowY = 'hidden'),
        this._element.classList.add(xn),
        this._queueCallback(() => {
          (this._element.classList.remove(xn),
          this._queueCallback(() => {
            this._element.style.overflowY = e;
          }, this._dialog));
        }, this._dialog),
        this._element.focus());
    }

    _adjustDialog() {
      const t =
          this._element.scrollHeight > document.documentElement.clientHeight;
      const e = this._scrollBar.getWidth();
      const i = e > 0;
      if (i && !t) {
        const t = Xt() ? 'paddingLeft' : 'paddingRight';
        this._element.style[t] = `${e}px`;
      }
      if (!i && t) {
        const t = Xt() ? 'paddingRight' : 'paddingLeft';
        this._element.style[t] = `${e}px`;
      }
    }

    _resetAdjustments() {
      ((this._element.style.paddingLeft = ''),
      (this._element.style.paddingRight = ''));
    }

    static jQueryInterface(t, e) {
      return this.each(function() {
        const i = Sn.getOrCreateInstance(this, t);
        if (typeof t === 'string') {
          if (void 0 === i[t]) {
            throw new TypeError(`No method named "${t}"`);
          }
          i[t](e);
        }
      });
    }
  }
  (pe.on(document, Cn, '[data-bs-toggle="modal"]', function(t) {
    const e = Ae.getElementFromSelector(this);
    (['A', 'AREA'].includes(this.tagName) && t.preventDefault(),
    pe.one(e, bn, t => {
      t.defaultPrevented ||
          pe.one(e, _n, () => {
            Bt(this) && this.focus();
          });
    }));
    const i = Ae.findOne('.modal.show');
    i && Sn.getInstance(i).hide();
    Sn.getOrCreateInstance(e).toggle(this);
  }),
  Ee(Sn),
  Yt(Sn));
  const Dn = '.bs.offcanvas';
  const $n = '.data-api';
  const In = `load${Dn}${$n}`;
  const Nn = 'show';
  const Pn = 'showing';
  const Mn = 'hiding';
  const jn = '.offcanvas.show';
  const Fn = `show${Dn}`;
  const Hn = `shown${Dn}`;
  const Wn = `hide${Dn}`;
  const Bn = `hidePrevented${Dn}`;
  const zn = `hidden${Dn}`;
  const Rn = `resize${Dn}`;
  const qn = `click${Dn}${$n}`;
  const Vn = `keydown.dismiss${Dn}`;
  const Kn = { backdrop: !0, keyboard: !0, scroll: !1 };
  const Qn = {
    backdrop: '(boolean|string)',
    keyboard: 'boolean',
    scroll: 'boolean',
  };
  class Xn extends ye {
    constructor(t, e) {
      (super(t, e),
      (this._isShown = !1),
      (this._backdrop = this._initializeBackDrop()),
      (this._focustrap = this._initializeFocusTrap()),
      this._addEventListeners());
    }

    static get Default() {
      return Kn;
    }

    static get DefaultType() {
      return Qn;
    }

    static get NAME() {
      return 'offcanvas';
    }

    toggle(t) {
      return this._isShown ? this.hide() : this.show(t);
    }

    show(t) {
      if (this._isShown) {
        return;
      }
      if (pe.trigger(this._element, Fn, { relatedTarget: t }).defaultPrevented) {
        return;
      }
      ((this._isShown = !0),
      this._backdrop.show(),
      this._config.scroll || new fn().hide(),
      this._element.setAttribute('aria-modal', !0),
      this._element.setAttribute('role', 'dialog'),
      this._element.classList.add(Pn));
      this._queueCallback(
        () => {
          ((this._config.scroll && !this._config.backdrop) ||
            this._focustrap.activate(),
          this._element.classList.add(Nn),
          this._element.classList.remove(Pn),
          pe.trigger(this._element, Hn, { relatedTarget: t }));
        },
        this._element,
        !0,
      );
    }

    hide() {
      if (!this._isShown) {
        return;
      }
      if (pe.trigger(this._element, Wn).defaultPrevented) {
        return;
      }
      (this._focustrap.deactivate(),
      this._element.blur(),
      (this._isShown = !1),
      this._element.classList.add(Mn),
      this._backdrop.hide());
      this._queueCallback(
        () => {
          (this._element.classList.remove(Nn, Mn),
          this._element.removeAttribute('aria-modal'),
          this._element.removeAttribute('role'),
          this._config.scroll || new fn().reset(),
          pe.trigger(this._element, zn));
        },
        this._element,
        !0,
      );
    }

    dispose() {
      (this._backdrop.dispose(), this._focustrap.deactivate(), super.dispose());
    }

    _initializeBackDrop() {
      const t = Boolean(this._config.backdrop);
      return new tn({
        className: 'offcanvas-backdrop',
        isVisible: t,
        isAnimated: !0,
        rootElement: this._element.parentNode,
        clickCallback: t ?
          () => {
            this._config.backdrop !== 'static' ?
              this.hide() :
              pe.trigger(this._element, Bn);
          } :
          null,
      });
    }

    _initializeFocusTrap() {
      return new ln({ trapElement: this._element });
    }

    _addEventListeners() {
      pe.on(this._element, Vn, t => {
        t.key === 'Escape' &&
          (this._config.keyboard ? this.hide() : pe.trigger(this._element, Bn));
      });
    }

    static jQueryInterface(t) {
      return this.each(function() {
        const e = Xn.getOrCreateInstance(this, t);
        if (typeof t === 'string') {
          if (void 0 === e[t] || t.startsWith('_') || t === 'constructor') {
            throw new TypeError(`No method named "${t}"`);
          }
          e[t](this);
        }
      });
    }
  }
  (pe.on(document, qn, '[data-bs-toggle="offcanvas"]', function(t) {
    const e = Ae.getElementFromSelector(this);
    if ((['A', 'AREA'].includes(this.tagName) && t.preventDefault(), zt(this))) {
      return;
    }
    pe.one(e, zn, () => {
      Bt(this) && this.focus();
    });
    const i = Ae.findOne(jn);
    i && i !== e && Xn.getInstance(i).hide();
    Xn.getOrCreateInstance(e).toggle(this);
  }),
  pe.on(window, In, () => {
    for (const t of Ae.find(jn)) {
      Xn.getOrCreateInstance(t).show();
    }
  }),
  pe.on(window, Rn, () => {
    for (const t of Ae.find('[aria-modal][class*=show][class*=offcanvas-]')) {
      getComputedStyle(t).position !== 'fixed' &&
          Xn.getOrCreateInstance(t).hide();
    }
  }),
  Ee(Xn),
  Yt(Xn));
  const Yn = {
    '*': ['class', 'dir', 'id', 'lang', 'role', /^aria-[\w-]*$/i],
    a: ['target', 'href', 'title', 'rel'],
    area: [],
    b: [],
    br: [],
    col: [],
    code: [],
    dd: [],
    div: [],
    dl: [],
    dt: [],
    em: [],
    hr: [],
    h1: [],
    h2: [],
    h3: [],
    h4: [],
    h5: [],
    h6: [],
    i: [],
    img: ['src', 'srcset', 'alt', 'title', 'width', 'height'],
    li: [],
    ol: [],
    p: [],
    pre: [],
    s: [],
    small: [],
    span: [],
    sub: [],
    sup: [],
    strong: [],
    u: [],
    ul: [],
  };
  const Un = new Set([
    'background',
    'cite',
    'href',
    'itemtype',
    'longdesc',
    'poster',
    'src',
    'xlink:href',
  ]);
  const Gn = /^(?!javascript:)(?:[a-z0-9+.-]+:|[^&:/?#]*(?:[/?#]|$))/i;
  const Jn = (t, e) => {
    const i = t.nodeName.toLowerCase();
    return e.includes(i) ?
      !Un.has(i) || Boolean(Gn.test(t.nodeValue)) :
      e.filter(t => t instanceof RegExp).some(t => t.test(i));
  };
  const Zn = {
    allowList: Yn,
    content: {},
    extraClass: '',
    html: !1,
    sanitize: !0,
    sanitizeFn: null,
    template: '<div></div>',
  };
  const ts = {
    allowList: 'object',
    content: 'object',
    extraClass: '(string|function)',
    html: 'boolean',
    sanitize: 'boolean',
    sanitizeFn: '(null|function)',
    template: 'string',
  };
  const es = {
    entry: '(string|element|function|null)',
    selector: '(string|element)',
  };
  class is extends ve {
    constructor(t) {
      (super(), (this._config = this._getConfig(t)));
    }

    static get Default() {
      return Zn;
    }

    static get DefaultType() {
      return ts;
    }

    static get NAME() {
      return 'TemplateFactory';
    }

    getContent() {
      return Object.values(this._config.content)
        .map(t => this._resolvePossibleFunction(t))
        .filter(Boolean);
    }

    hasContent() {
      return this.getContent().length > 0;
    }

    changeContent(t) {
      return (
        this._checkContent(t),
        (this._config.content = { ...this._config.content, ...t }),
        this
      );
    }

    toHtml() {
      const t = document.createElement('div');
      t.innerHTML = this._maybeSanitize(this._config.template);
      for (const [e, i] of Object.entries(this._config.content)) {
        this._setContent(t, i, e);
      }
      const e = t.children[0];
      const i = this._resolvePossibleFunction(this._config.extraClass);
      return (i && e.classList.add(...i.split(' ')), e);
    }

    _typeCheckConfig(t) {
      (super._typeCheckConfig(t), this._checkContent(t.content));
    }

    _checkContent(t) {
      for (const [e, i] of Object.entries(t)) {
        super._typeCheckConfig({ selector: e, entry: i }, es);
      }
    }

    _setContent(t, e, i) {
      const n = Ae.findOne(i, t);
      n &&
        ((e = this._resolvePossibleFunction(e)) ?
          Ht(e) ?
            this._putElementInTemplate(Wt(e), n) :
            this._config.html ?
              (n.innerHTML = this._maybeSanitize(e)) :
              (n.textContent = e) :
          n.remove());
    }

    _maybeSanitize(t) {
      return this._config.sanitize ?
        (function(t, e, i) {
          if (!t.length) {
            return t;
          }
          if (i && typeof i === 'function') {
            return i(t);
          }
          const n = new window.DOMParser().parseFromString(t, 'text/html');
          const s = [].concat(...n.body.querySelectorAll('*'));
          for (const t of s) {
            const i = t.nodeName.toLowerCase();
            if (!Object.keys(e).includes(i)) {
              t.remove();
              continue;
            }
            const n = [].concat(...t.attributes);
            const s = [].concat(e['*'] || [], e[i] || []);
            for (const e of n) {
              Jn(e, s) || t.removeAttribute(e.nodeName);
            }
          }
          return n.body.innerHTML;
        })(t, this._config.allowList, this._config.sanitizeFn) :
        t;
    }

    _resolvePossibleFunction(t) {
      return Ut(t, [void 0, this]);
    }

    _putElementInTemplate(t, e) {
      if (this._config.html) {
        return ((e.innerHTML = ''), void e.append(t));
      }
      e.textContent = t.textContent;
    }
  }
  const ns = new Set(['sanitize', 'allowList', 'sanitizeFn']);
  const ss = 'fade';
  const os = 'show';
  const rs = '.tooltip-inner';
  const as = '.modal';
  const ls = 'hide.bs.modal';
  const cs = 'hover';
  const hs = 'focus';
  const us = 'click';
  const ds = {
    AUTO: 'auto',
    TOP: 'top',
    RIGHT: Xt() ? 'left' : 'right',
    BOTTOM: 'bottom',
    LEFT: Xt() ? 'right' : 'left',
  };
  const fs = {
    allowList: Yn,
    animation: !0,
    boundary: 'clippingParents',
    container: !1,
    customClass: '',
    delay: 0,
    fallbackPlacements: ['top', 'right', 'bottom', 'left'],
    html: !1,
    offset: [0, 6],
    placement: 'top',
    popperConfig: null,
    sanitize: !0,
    sanitizeFn: null,
    selector: !1,
    template:
        '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
    title: '',
    trigger: 'hover focus',
  };
  const ps = {
    allowList: 'object',
    animation: 'boolean',
    boundary: '(string|element)',
    container: '(string|element|boolean)',
    customClass: '(string|function)',
    delay: '(number|object)',
    fallbackPlacements: 'array',
    html: 'boolean',
    offset: '(array|string|function)',
    placement: '(string|function)',
    popperConfig: '(null|object|function)',
    sanitize: 'boolean',
    sanitizeFn: '(null|function)',
    selector: '(string|boolean)',
    template: 'string',
    title: '(string|element|function)',
    trigger: 'string',
  };
  class ms extends ye {
    constructor(t, e) {
      (super(t, e),
      (this._isEnabled = !0),
      (this._timeout = 0),
      (this._isHovered = null),
      (this._activeTrigger = {}),
      (this._popper = null),
      (this._templateFactory = null),
      (this._newContent = null),
      (this.tip = null),
      this._setListeners(),
      this._config.selector || this._fixTitle());
    }

    static get Default() {
      return fs;
    }

    static get DefaultType() {
      return ps;
    }

    static get NAME() {
      return 'tooltip';
    }

    enable() {
      this._isEnabled = !0;
    }

    disable() {
      this._isEnabled = !1;
    }

    toggleEnabled() {
      this._isEnabled = !this._isEnabled;
    }

    toggle() {
      this._isEnabled && (this._isShown() ? this._leave() : this._enter());
    }

    dispose() {
      (clearTimeout(this._timeout),
      pe.off(this._element.closest(as), ls, this._hideModalHandler),
      this._element.getAttribute('data-bs-original-title') &&
          this._element.setAttribute(
            'title',
            this._element.getAttribute('data-bs-original-title'),
          ),
      this._disposePopper(),
      super.dispose());
    }

    show() {
      if (this._element.style.display === 'none') {
        throw new Error('Please use show on visible elements');
      }
      if (!this._isWithContent() || !this._isEnabled) {
        return;
      }
      const t = pe.trigger(this._element, this.constructor.eventName('show'));
      const e = (
        Rt(this._element) || this._element.ownerDocument.documentElement
      ).contains(this._element);
      if (t.defaultPrevented || !e) {
        return;
      }
      this._disposePopper();
      const i = this._getTipElement();
      this._element.setAttribute('aria-describedby', i.getAttribute('id'));
      const { container: n } = this._config;
      if (
        (this._element.ownerDocument.documentElement.contains(this.tip) ||
          (n.append(i),
          pe.trigger(this._element, this.constructor.eventName('inserted'))),
        (this._popper = this._createPopper(i)),
        i.classList.add(os),
        'ontouchstart' in document.documentElement)
      ) {
        for (const t of [].concat(...document.body.children)) {
          pe.on(t, 'mouseover', qt);
        }
      }
      this._queueCallback(
        () => {
          (pe.trigger(this._element, this.constructor.eventName('shown')),
          !1 === this._isHovered && this._leave(),
          (this._isHovered = !1));
        },
        this.tip,
        this._isAnimated(),
      );
    }

    hide() {
      if (!this._isShown()) {
        return;
      }
      if (
        pe.trigger(this._element, this.constructor.eventName('hide'))
          .defaultPrevented
      ) {
        return;
      }
      if (
        (this._getTipElement().classList.remove(os),
        'ontouchstart' in document.documentElement)
      ) {
        for (const t of [].concat(...document.body.children)) {
          pe.off(t, 'mouseover', qt);
        }
      }
      ((this._activeTrigger[us] = !1),
      (this._activeTrigger[hs] = !1),
      (this._activeTrigger[cs] = !1),
      (this._isHovered = null));
      this._queueCallback(
        () => {
          this._isWithActiveTrigger() ||
            (this._isHovered || this._disposePopper(),
            this._element.removeAttribute('aria-describedby'),
            pe.trigger(this._element, this.constructor.eventName('hidden')));
        },
        this.tip,
        this._isAnimated(),
      );
    }

    update() {
      this._popper && this._popper.update();
    }

    _isWithContent() {
      return Boolean(this._getTitle());
    }

    _getTipElement() {
      return (
        this.tip ||
          (this.tip = this._createTipElement(
            this._newContent || this._getContentForTemplate(),
          )),
        this.tip
      );
    }

    _createTipElement(t) {
      const e = this._getTemplateFactory(t).toHtml();
      if (!e) {
        return null;
      }
      (e.classList.remove(ss, os),
      e.classList.add(`bs-${this.constructor.NAME}-auto`));
      const i = (t => {
        do {
          t += Math.floor(1e6 * Math.random());
        } while (document.getElementById(t));
        return t;
      })(this.constructor.NAME).toString();
      return (
        e.setAttribute('id', i),
        this._isAnimated() && e.classList.add(ss),
        e
      );
    }

    setContent(t) {
      ((this._newContent = t),
      this._isShown() && (this._disposePopper(), this.show()));
    }

    _getTemplateFactory(t) {
      return (
        this._templateFactory ?
          this._templateFactory.changeContent(t) :
          (this._templateFactory = new is({
            ...this._config,
            content: t,
            extraClass: this._resolvePossibleFunction(
              this._config.customClass,
            ),
          })),
        this._templateFactory
      );
    }

    _getContentForTemplate() {
      return { [rs]: this._getTitle() };
    }

    _getTitle() {
      return (
        this._resolvePossibleFunction(this._config.title) ||
        this._element.getAttribute('data-bs-original-title')
      );
    }

    _initializeOnDelegatedTarget(t) {
      return this.constructor.getOrCreateInstance(
        t.delegateTarget,
        this._getDelegateConfig(),
      );
    }

    _isAnimated() {
      return (
        this._config.animation || (this.tip && this.tip.classList.contains(ss))
      );
    }

    _isShown() {
      return this.tip && this.tip.classList.contains(os);
    }

    _createPopper(t) {
      const e = Ut(this._config.placement, [this, t, this._element]);
      const i = ds[e.toUpperCase()];
      return Dt(this._element, t, this._getPopperConfig(i));
    }

    _getOffset() {
      const { offset: t } = this._config;
      return typeof t === 'string' ?
        t.split(',').map(t => Number.parseInt(t, 10)) :
        typeof t === 'function' ?
          e => t(e, this._element) :
          t;
    }

    _resolvePossibleFunction(t) {
      return Ut(t, [this._element, this._element]);
    }

    _getPopperConfig(t) {
      const e = {
        placement: t,
        modifiers: [
          {
            name: 'flip',
            options: { fallbackPlacements: this._config.fallbackPlacements },
          },
          { name: 'offset', options: { offset: this._getOffset() } },
          {
            name: 'preventOverflow',
            options: { boundary: this._config.boundary },
          },
          {
            name: 'arrow',
            options: { element: `.${this.constructor.NAME}-arrow` },
          },
          {
            name: 'preSetPlacement',
            enabled: !0,
            phase: 'beforeMain',
            fn: t => {
              this._getTipElement().setAttribute(
                'data-popper-placement',
                t.state.placement,
              );
            },
          },
        ],
      };
      return { ...e, ...Ut(this._config.popperConfig, [void 0, e]) };
    }

    _setListeners() {
      const t = this._config.trigger.split(' ');
      for (const e of t) {
        if (e === 'click') {
          pe.on(
            this._element,
            this.constructor.eventName('click'),
            this._config.selector,
            t => {
              const e = this._initializeOnDelegatedTarget(t);
              ((e._activeTrigger[us] = !(e._isShown() && e._activeTrigger[us])),
              e.toggle());
            },
          );
        } else if (e !== 'manual') {
          const t =
              e === cs ?
                this.constructor.eventName('mouseenter') :
                this.constructor.eventName('focusin');
          const i =
              e === cs ?
                this.constructor.eventName('mouseleave') :
                this.constructor.eventName('focusout');
          (pe.on(this._element, t, this._config.selector, t => {
            const e = this._initializeOnDelegatedTarget(t);
            ((e._activeTrigger[t.type === 'focusin' ? hs : cs] = !0),
            e._enter());
          }),
          pe.on(this._element, i, this._config.selector, t => {
            const e = this._initializeOnDelegatedTarget(t);
            ((e._activeTrigger[t.type === 'focusout' ? hs : cs] =
                e._element.contains(t.relatedTarget)),
            e._leave());
          }));
        }
      }
      ((this._hideModalHandler = () => {
        this._element && this.hide();
      }),
      pe.on(this._element.closest(as), ls, this._hideModalHandler));
    }

    _fixTitle() {
      const t = this._element.getAttribute('title');
      t &&
        (this._element.getAttribute('aria-label') ||
          this._element.textContent.trim() ||
          this._element.setAttribute('aria-label', t),
        this._element.setAttribute('data-bs-original-title', t),
        this._element.removeAttribute('title'));
    }

    _enter() {
      this._isShown() || this._isHovered ?
        (this._isHovered = !0) :
        ((this._isHovered = !0),
        this._setTimeout(() => {
          this._isHovered && this.show();
        }, this._config.delay.show));
    }

    _leave() {
      this._isWithActiveTrigger() ||
        ((this._isHovered = !1),
        this._setTimeout(() => {
          this._isHovered || this.hide();
        }, this._config.delay.hide));
    }

    _setTimeout(t, e) {
      (clearTimeout(this._timeout), (this._timeout = setTimeout(t, e)));
    }

    _isWithActiveTrigger() {
      return Object.values(this._activeTrigger).includes(!0);
    }

    _getConfig(t) {
      const e = be.getDataAttributes(this._element);
      for (const t of Object.keys(e)) {
        ns.has(t) && delete e[t];
      }
      return (
        (t = { ...e, ...(typeof t === 'object' && t ? t : {}) }),
        (t = this._mergeConfigObj(t)),
        (t = this._configAfterMerge(t)),
        this._typeCheckConfig(t),
        t
      );
    }

    _configAfterMerge(t) {
      return (
        (t.container = !1 === t.container ? document.body : Wt(t.container)),
        typeof t.delay === 'number' &&
          (t.delay = { show: t.delay, hide: t.delay }),
        typeof t.title === 'number' && (t.title = t.title.toString()),
        typeof t.content === 'number' && (t.content = t.content.toString()),
        t
      );
    }

    _getDelegateConfig() {
      const t = {};
      for (const [e, i] of Object.entries(this._config)) {
        this.constructor.Default[e] !== i && (t[e] = i);
      }
      return ((t.selector = !1), (t.trigger = 'manual'), t);
    }

    _disposePopper() {
      (this._popper && (this._popper.destroy(), (this._popper = null)),
      this.tip && (this.tip.remove(), (this.tip = null)));
    }

    static jQueryInterface(t) {
      return this.each(function() {
        const e = ms.getOrCreateInstance(this, t);
        if (typeof t === 'string') {
          if (void 0 === e[t]) {
            throw new TypeError(`No method named "${t}"`);
          }
          e[t]();
        }
      });
    }
  }
  Yt(ms);
  const gs = '.popover-header';
  const _s = '.popover-body';
  const bs = {
    ...ms.Default,
    content: '',
    offset: [0, 8],
    placement: 'right',
    template:
        '<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
    trigger: 'click',
  };
  const vs = { ...ms.DefaultType, content: '(null|string|element|function)' };
  class ys extends ms {
    static get Default() {
      return bs;
    }

    static get DefaultType() {
      return vs;
    }

    static get NAME() {
      return 'popover';
    }

    _isWithContent() {
      return this._getTitle() || this._getContent();
    }

    _getContentForTemplate() {
      return { [gs]: this._getTitle(), [_s]: this._getContent() };
    }

    _getContent() {
      return this._resolvePossibleFunction(this._config.content);
    }

    static jQueryInterface(t) {
      return this.each(function() {
        const e = ys.getOrCreateInstance(this, t);
        if (typeof t === 'string') {
          if (void 0 === e[t]) {
            throw new TypeError(`No method named "${t}"`);
          }
          e[t]();
        }
      });
    }
  }
  Yt(ys);
  const ws = '.bs.scrollspy';
  const As = `activate${ws}`;
  const Es = `click${ws}`;
  const Cs = `load${ws}.data-api`;
  const Ts = 'active';
  const Os = '[href]';
  const xs = '.nav-link';
  const ks = `${xs}, .nav-item > ${xs}, .list-group-item`;
  const Ls = {
    offset: null,
    rootMargin: '0px 0px -25%',
    smoothScroll: !1,
    target: null,
    threshold: [0.1, 0.5, 1],
  };
  const Ss = {
    offset: '(number|null)',
    rootMargin: 'string',
    smoothScroll: 'boolean',
    target: 'element',
    threshold: 'array',
  };
  class Ds extends ye {
    constructor(t, e) {
      (super(t, e),
      (this._targetLinks = new Map()),
      (this._observableSections = new Map()),
      (this._rootElement =
          getComputedStyle(this._element).overflowY === 'visible' ?
            null :
            this._element),
      (this._activeTarget = null),
      (this._observer = null),
      (this._previousScrollData = { visibleEntryTop: 0, parentScrollTop: 0 }),
      this.refresh());
    }

    static get Default() {
      return Ls;
    }

    static get DefaultType() {
      return Ss;
    }

    static get NAME() {
      return 'scrollspy';
    }

    refresh() {
      (this._initializeTargetsAndObservables(),
      this._maybeEnableSmoothScroll(),
      this._observer ?
        this._observer.disconnect() :
        (this._observer = this._getNewObserver()));
      for (const t of this._observableSections.values()) {
        this._observer.observe(t);
      }
    }

    dispose() {
      (this._observer.disconnect(), super.dispose());
    }

    _configAfterMerge(t) {
      return (
        (t.target = Wt(t.target) || document.body),
        (t.rootMargin = t.offset ? `${t.offset}px 0px -30%` : t.rootMargin),
        typeof t.threshold === 'string' &&
          (t.threshold = t.threshold
            .split(',')
            .map(t => Number.parseFloat(t))),
        t
      );
    }

    _maybeEnableSmoothScroll() {
      this._config.smoothScroll &&
        (pe.off(this._config.target, Es),
        pe.on(this._config.target, Es, Os, t => {
          const e = this._observableSections.get(t.target.hash);
          if (e) {
            t.preventDefault();
            const i = this._rootElement || window;
            const n = e.offsetTop - this._element.offsetTop;
            if (i.scrollTo) {
              return void i.scrollTo({ top: n, behavior: 'smooth' });
            }
            i.scrollTop = n;
          }
        }));
    }

    _getNewObserver() {
      const t = {
        root: this._rootElement,
        threshold: this._config.threshold,
        rootMargin: this._config.rootMargin,
      };
      return new IntersectionObserver(t => this._observerCallback(t), t);
    }

    _observerCallback(t) {
      const e = t => this._targetLinks.get(`#${t.target.id}`);
      const i = t => {
        ((this._previousScrollData.visibleEntryTop = t.target.offsetTop),
        this._process(e(t)));
      };
      const n = (this._rootElement || document.documentElement).scrollTop;
      const s = n >= this._previousScrollData.parentScrollTop;
      this._previousScrollData.parentScrollTop = n;
      for (const o of t) {
        if (!o.isIntersecting) {
          ((this._activeTarget = null), this._clearActiveClass(e(o)));
          continue;
        }
        const t =
          o.target.offsetTop >= this._previousScrollData.visibleEntryTop;
        if (s && t) {
          if ((i(o), !n)) {
            return;
          }
        } else {
          s || t || i(o);
        }
      }
    }

    _initializeTargetsAndObservables() {
      ((this._targetLinks = new Map()), (this._observableSections = new Map()));
      const t = Ae.find(Os, this._config.target);
      for (const e of t) {
        if (!e.hash || zt(e)) {
          continue;
        }
        const t = Ae.findOne(decodeURI(e.hash), this._element);
        Bt(t) &&
          (this._targetLinks.set(decodeURI(e.hash), e),
          this._observableSections.set(e.hash, t));
      }
    }

    _process(t) {
      this._activeTarget !== t &&
        (this._clearActiveClass(this._config.target),
        (this._activeTarget = t),
        t.classList.add(Ts),
        this._activateParents(t),
        pe.trigger(this._element, As, { relatedTarget: t }));
    }

    _activateParents(t) {
      if (t.classList.contains('dropdown-item')) {
        Ae.findOne('.dropdown-toggle', t.closest('.dropdown')).classList.add(
          Ts,
        );
      } else {
        for (const e of Ae.parents(t, '.nav, .list-group')) {
          for (const t of Ae.prev(e, ks)) {
            t.classList.add(Ts);
          }
        }
      }
    }

    _clearActiveClass(t) {
      t.classList.remove(Ts);
      const e = Ae.find(`${Os}.${Ts}`, t);
      for (const t of e) {
        t.classList.remove(Ts);
      }
    }

    static jQueryInterface(t) {
      return this.each(function() {
        const e = Ds.getOrCreateInstance(this, t);
        if (typeof t === 'string') {
          if (void 0 === e[t] || t.startsWith('_') || t === 'constructor') {
            throw new TypeError(`No method named "${t}"`);
          }
          e[t]();
        }
      });
    }
  }
  (pe.on(window, Cs, () => {
    for (const t of Ae.find('[data-bs-spy="scroll"]')) {
      Ds.getOrCreateInstance(t);
    }
  }),
  Yt(Ds));
  const $s = '.bs.tab';
  const Is = `hide${$s}`;
  const Ns = `hidden${$s}`;
  const Ps = `show${$s}`;
  const Ms = `shown${$s}`;
  const js = `click${$s}`;
  const Fs = `keydown${$s}`;
  const Hs = `load${$s}`;
  const Ws = 'ArrowLeft';
  const Bs = 'ArrowRight';
  const zs = 'ArrowUp';
  const Rs = 'ArrowDown';
  const qs = 'Home';
  const Vs = 'End';
  const Ks = 'active';
  const Qs = 'fade';
  const Xs = 'show';
  const Ys = '.dropdown-toggle';
  const Us = `:not(${Ys})`;
  const Gs =
      '[data-bs-toggle="tab"], [data-bs-toggle="pill"], [data-bs-toggle="list"]';
  const Js = `${`.nav-link${Us}, .list-group-item${Us}, [role="tab"]${Us}`}, ${Gs}`;
  const Zs = `.${Ks}[data-bs-toggle="tab"], .${Ks}[data-bs-toggle="pill"], .${Ks}[data-bs-toggle="list"]`;
  class to extends ye {
    constructor(t) {
      (super(t),
      (this._parent = this._element.closest(
        '.list-group, .nav, [role="tablist"]',
      )),
      this._parent &&
          (this._setInitialAttributes(this._parent, this._getChildren()),
          pe.on(this._element, Fs, t => this._keydown(t))));
    }

    static get NAME() {
      return 'tab';
    }

    show() {
      const t = this._element;
      if (this._elemIsActive(t)) {
        return;
      }
      const e = this._getActiveElem();
      const i = e ? pe.trigger(e, Is, { relatedTarget: t }) : null;
      pe.trigger(t, Ps, { relatedTarget: e }).defaultPrevented ||
        (i && i.defaultPrevented) ||
        (this._deactivate(e, t), this._activate(t, e));
    }

    _activate(t, e) {
      if (!t) {
        return;
      }
      (t.classList.add(Ks), this._activate(Ae.getElementFromSelector(t)));
      this._queueCallback(
        () => {
          t.getAttribute('role') === 'tab' ?
            (t.removeAttribute('tabindex'),
            t.setAttribute('aria-selected', !0),
            this._toggleDropDown(t, !0),
            pe.trigger(t, Ms, { relatedTarget: e })) :
            t.classList.add(Xs);
        },
        t,
        t.classList.contains(Qs),
      );
    }

    _deactivate(t, e) {
      if (!t) {
        return;
      }
      (t.classList.remove(Ks),
      t.blur(),
      this._deactivate(Ae.getElementFromSelector(t)));
      this._queueCallback(
        () => {
          t.getAttribute('role') === 'tab' ?
            (t.setAttribute('aria-selected', !1),
            t.setAttribute('tabindex', '-1'),
            this._toggleDropDown(t, !1),
            pe.trigger(t, Ns, { relatedTarget: e })) :
            t.classList.remove(Xs);
        },
        t,
        t.classList.contains(Qs),
      );
    }

    _keydown(t) {
      if (![Ws, Bs, zs, Rs, qs, Vs].includes(t.key)) {
        return;
      }
      (t.stopPropagation(), t.preventDefault());
      const e = this._getChildren().filter(t => !zt(t));
      let i;
      if ([qs, Vs].includes(t.key)) {
        i = e[t.key === qs ? 0 : e.length - 1];
      } else {
        const n = [Bs, Rs].includes(t.key);
        i = Jt(e, t.target, n, !0);
      }
      i && (i.focus({ preventScroll: !0 }), to.getOrCreateInstance(i).show());
    }

    _getChildren() {
      return Ae.find(Js, this._parent);
    }

    _getActiveElem() {
      return this._getChildren().find(t => this._elemIsActive(t)) || null;
    }

    _setInitialAttributes(t, e) {
      this._setAttributeIfNotExists(t, 'role', 'tablist');
      for (const t of e) {
        this._setInitialAttributesOnChild(t);
      }
    }

    _setInitialAttributesOnChild(t) {
      t = this._getInnerElement(t);
      const e = this._elemIsActive(t);
      const i = this._getOuterElement(t);
      (t.setAttribute('aria-selected', e),
      i !== t && this._setAttributeIfNotExists(i, 'role', 'presentation'),
      e || t.setAttribute('tabindex', '-1'),
      this._setAttributeIfNotExists(t, 'role', 'tab'),
      this._setInitialAttributesOnTargetPanel(t));
    }

    _setInitialAttributesOnTargetPanel(t) {
      const e = Ae.getElementFromSelector(t);
      e &&
        (this._setAttributeIfNotExists(e, 'role', 'tabpanel'),
        t.id && this._setAttributeIfNotExists(e, 'aria-labelledby', `${t.id}`));
    }

    _toggleDropDown(t, e) {
      const i = this._getOuterElement(t);
      if (!i.classList.contains('dropdown')) {
        return;
      }
      const n = (t, n) => {
        const s = Ae.findOne(t, i);
        s && s.classList.toggle(n, e);
      };
      (n(Ys, Ks), n('.dropdown-menu', Xs), i.setAttribute('aria-expanded', e));
    }

    _setAttributeIfNotExists(t, e, i) {
      t.hasAttribute(e) || t.setAttribute(e, i);
    }

    _elemIsActive(t) {
      return t.classList.contains(Ks);
    }

    _getInnerElement(t) {
      return t.matches(Js) ? t : Ae.findOne(Js, t);
    }

    _getOuterElement(t) {
      return t.closest('.nav-item, .list-group-item') || t;
    }

    static jQueryInterface(t) {
      return this.each(function() {
        const e = to.getOrCreateInstance(this);
        if (typeof t === 'string') {
          if (void 0 === e[t] || t.startsWith('_') || t === 'constructor') {
            throw new TypeError(`No method named "${t}"`);
          }
          e[t]();
        }
      });
    }
  }
  (pe.on(document, js, Gs, function(t) {
    (['A', 'AREA'].includes(this.tagName) && t.preventDefault(),
    zt(this) || to.getOrCreateInstance(this).show());
  }),
  pe.on(window, Hs, () => {
    for (const t of Ae.find(Zs)) {
      to.getOrCreateInstance(t);
    }
  }),
  Yt(to));
  const eo = '.bs.toast';
  const io = `mouseover${eo}`;
  const no = `mouseout${eo}`;
  const so = `focusin${eo}`;
  const oo = `focusout${eo}`;
  const ro = `hide${eo}`;
  const ao = `hidden${eo}`;
  const lo = `show${eo}`;
  const co = `shown${eo}`;
  const ho = 'hide';
  const uo = 'show';
  const fo = 'showing';
  const po = { animation: 'boolean', autohide: 'boolean', delay: 'number' };
  const mo = { animation: !0, autohide: !0, delay: 5e3 };
  class go extends ye {
    constructor(t, e) {
      (super(t, e),
      (this._timeout = null),
      (this._hasMouseInteraction = !1),
      (this._hasKeyboardInteraction = !1),
      this._setListeners());
    }

    static get Default() {
      return mo;
    }

    static get DefaultType() {
      return po;
    }

    static get NAME() {
      return 'toast';
    }

    show() {
      if (pe.trigger(this._element, lo).defaultPrevented) {
        return;
      }
      (this._clearTimeout(),
      this._config.animation && this._element.classList.add('fade'));
      (this._element.classList.remove(ho),
      Vt(this._element),
      this._element.classList.add(uo, fo),
      this._queueCallback(
        () => {
          (this._element.classList.remove(fo),
          pe.trigger(this._element, co),
          this._maybeScheduleHide());
        },
        this._element,
        this._config.animation,
      ));
    }

    hide() {
      if (!this.isShown()) {
        return;
      }
      if (pe.trigger(this._element, ro).defaultPrevented) {
        return;
      }
      (this._element.classList.add(fo),
      this._queueCallback(
        () => {
          (this._element.classList.add(ho),
          this._element.classList.remove(fo, uo),
          pe.trigger(this._element, ao));
        },
        this._element,
        this._config.animation,
      ));
    }

    dispose() {
      (this._clearTimeout(),
      this.isShown() && this._element.classList.remove(uo),
      super.dispose());
    }

    isShown() {
      return this._element.classList.contains(uo);
    }

    _maybeScheduleHide() {
      this._config.autohide &&
        (this._hasMouseInteraction ||
          this._hasKeyboardInteraction ||
          (this._timeout = setTimeout(() => {
            this.hide();
          }, this._config.delay)));
    }

    _onInteraction(t, e) {
      switch (t.type) {
      case 'mouseover':
      case 'mouseout':
        this._hasMouseInteraction = e;
        break;
      case 'focusin':
      case 'focusout':
        this._hasKeyboardInteraction = e;
      }
      if (e) {
        return void this._clearTimeout();
      }
      const i = t.relatedTarget;
      this._element === i ||
        this._element.contains(i) ||
        this._maybeScheduleHide();
    }

    _setListeners() {
      (pe.on(this._element, io, t => this._onInteraction(t, !0)),
      pe.on(this._element, no, t => this._onInteraction(t, !1)),
      pe.on(this._element, so, t => this._onInteraction(t, !0)),
      pe.on(this._element, oo, t => this._onInteraction(t, !1)));
    }

    _clearTimeout() {
      (clearTimeout(this._timeout), (this._timeout = null));
    }

    static jQueryInterface(t) {
      return this.each(function() {
        const e = go.getOrCreateInstance(this, t);
        if (typeof t === 'string') {
          if (void 0 === e[t]) {
            throw new TypeError(`No method named "${t}"`);
          }
          e[t](this);
        }
      });
    }
  }
  (Ee(go),
  Yt(go),
  document.addEventListener('DOMContentLoaded', () => {}));
})();
