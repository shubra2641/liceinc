/*! For license information please see app.js.LICENSE.txt */
// security-ignore: COMPILED_JS (Minified library code)
// security-ignore: COMPILED_JS (Minified library code)
(() => {
  'use strict';
  let t;
  const e = {
    442: () => {},
    796: () => {},
    929: (t, e, i) => {
      const n = {};
      (i.r(n),
      i.d(n, {
        afterMain: () => E,
        afterRead: () => y,
        afterWrite: () => T,
        applyStyles: () => I,
        arrow: () => Z,
        auto: () => l,
        basePlacements: () => c,
        beforeMain: () => w,
        beforeRead: () => b,
        beforeWrite: () => C,
        bottom: () => o,
        clippingParents: () => d,
        computeStyles: () => nt,
        createPopper: () => It,
        createPopperBase: () => $t,
        createPopperLite: () => Nt,
        detectOverflow: () => vt,
        end: () => u,
        eventListeners: () => ot,
        flip: () => yt,
        hide: () => Et,
        left: () => a,
        main: () => A,
        modifierPhases: () => x,
        offset: () => Ct,
        placements: () => _,
        popper: () => p,
        popperGenerator: () => Dt,
        popperOffsets: () => Ot,
        preventOverflow: () => Tt,
        read: () => v,
        reference: () => m,
        right: () => r,
        start: () => h,
        top: () => s,
        variationPlacements: () => g,
        viewport: () => f,
        write: () => O,
      }));
      var s = 'top';
      var o = 'bottom';
      var r = 'right';
      var a = 'left';
      var l = 'auto';
      var c = [s, o, r, a];
      var h = 'start';
      var u = 'end';
      var d = 'clippingParents';
      var f = 'viewport';
      var p = 'popper';
      var m = 'reference';
      var g = c.reduce((t, e) => t.concat([`${e}-${h}`, `${e}-${u}`]), []);
      var _ = [].concat(c, [l]).reduce((t, e) => t.concat([e, `${e}-${h}`, `${e}-${u}`]), []);
      var b = 'beforeRead';
      var v = 'read';
      var y = 'afterRead';
      var w = 'beforeMain';
      var A = 'main';
      var E = 'afterMain';
      var C = 'beforeWrite';
      var O = 'write';
      var T = 'afterWrite';
      var x = [b, v, y, w, A, E, C, O, T];
      function k(t) {
        return t ? (t.nodeName || '').toLowerCase() : null;
      }
      function L(t) {
        if (t == null) {
          return window;
        }
        if (t.toString() !== '[object Window]') {
          const e = t.ownerDocument;
          return (e && e.defaultView) || window;
        }
        return t;
      }
      function S(t) {
        return t instanceof L(t).Element || t instanceof Element;
      }
      function D(t) {
        return t instanceof L(t).HTMLElement || t instanceof HTMLElement;
      }
      function $(t) {
        return (
          typeof ShadowRoot !== 'undefined' &&
            (t instanceof L(t).ShadowRoot || t instanceof ShadowRoot)
        );
      }
      const I = {
        name: 'applyStyles',
        enabled: !0,
        phase: 'write',
        fn: function(t) {
          const e = t.state;
          Object.keys(e.elements).forEach(t => {
            const i = e.styles[t] || {};
            const n = e.attributes[t] || {};
            const s = e.elements[t];
            D(s) &&
                k(s) &&
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
            e.elements.arrow &&
                Object.assign(e.elements.arrow.style, i.arrow),
            function() {
              Object.keys(e.elements).forEach(t => {
                const n = e.elements[t];
                const s = e.attributes[t] || {};
                const o = Object.keys(
                  e.styles.hasOwnProperty(t) ? e.styles[t] : i[t],
                ).reduce((t, e) => ((t[e] = ''), t), {});
                D(n) &&
                    k(n) &&
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
      function N(t) {
        return t.split('-')[0];
      }
      const P = Math.max;
      const j = Math.min;
      const M = Math.round;
      function F() {
        const t = navigator.userAgentData;
        return t != null && t.brands && Array.isArray(t.brands) ?
          t.brands
            .map(t => `${t.brand}/${t.version}`)
            .join(' ') :
          navigator.userAgent;
      }
      function H() {
        return !/^((?!chrome|android).)*safari/i.test(F());
      }
      function W(t, e, i) {
        (void 0 === e && (e = !1), void 0 === i && (i = !1));
        const n = t.getBoundingClientRect();
        let s = 1;
        let o = 1;
        e &&
            D(t) &&
            ((s = (t.offsetWidth > 0 && M(n.width) / t.offsetWidth) || 1),
            (o = (t.offsetHeight > 0 && M(n.height) / t.offsetHeight) || 1));
        const r = (S(t) ? L(t) : window).visualViewport;
        const a = !H() && i;
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
      function B(t) {
        const e = W(t);
        let i = t.offsetWidth;
        let n = t.offsetHeight;
        return (
          Math.abs(e.width - i) <= 1 && (i = e.width),
          Math.abs(e.height - n) <= 1 && (n = e.height),
          { x: t.offsetLeft, y: t.offsetTop, width: i, height: n }
        );
      }
      function z(t, e) {
        const i = e.getRootNode && e.getRootNode();
        if (t.contains(e)) {
          return !0;
        }
        if (i && $(i)) {
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
      function R(t) {
        return L(t).getComputedStyle(t);
      }
      function q(t) {
        return ['table', 'td', 'th'].indexOf(k(t)) >= 0;
      }
      function V(t) {
        return ((S(t) ? t.ownerDocument : t.document) || window.document)
          .documentElement;
      }
      function K(t) {
        return k(t) === 'html' ?
          t :
          t.assignedSlot || t.parentNode || ($(t) ? t.host : null) || V(t);
      }
      function Q(t) {
        return D(t) && R(t).position !== 'fixed' ? t.offsetParent : null;
      }
      function X(t) {
        for (
          var e = L(t), i = Q(t);
          i && q(i) && R(i).position === 'static';

        ) {
          i = Q(i);
        }
        return i &&
            (k(i) === 'html' || (k(i) === 'body' && R(i).position === 'static')) ?
          e :
          i ||
                (function(t) {
                  const e = /firefox/i.test(F());
                  if (/Trident/i.test(F()) && D(t) && R(t).position === 'fixed') {
                    return null;
                  }
                  let i = K(t);
                  for (
                    $(i) && (i = i.host);
                    D(i) && ['html', 'body'].indexOf(k(i)) < 0;

                  ) {
                    const n = R(i);
                    if (
                      n.transform !== 'none' ||
                      n.perspective !== 'none' ||
                      n.contain === 'paint' ||
                      ['transform', 'perspective'].indexOf(n.willChange) !==
                        -1 ||
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
      function Y(t) {
        return ['top', 'bottom'].indexOf(t) >= 0 ? 'x' : 'y';
      }
      function U(t, e, i) {
        return P(t, j(e, i));
      }
      function G(t) {
        return Object.assign({}, { top: 0, right: 0, bottom: 0, left: 0 }, t);
      }
      function J(t, e) {
        return e.reduce((e, i) => ((e[i] = t), e), {});
      }
      const Z = {
        name: 'arrow',
        enabled: !0,
        phase: 'main',
        fn: function(t) {
          let e;
          const i = t.state;
          const n = t.name;
          const l = t.options;
          const h = i.elements.arrow;
          const u = i.modifiersData.popperOffsets;
          const d = N(i.placement);
          const f = Y(d);
          const p = [a, r].indexOf(d) >= 0 ? 'height' : 'width';
          if (h && u) {
            const m = (function(t, e) {
              return G(
                typeof (t =
                        typeof t === 'function' ?
                          t(
                            Object.assign({}, e.rects, {
                              placement: e.placement,
                            }),
                          ) :
                          t) !==
                      'number' ?
                  t :
                  J(t, c),
              );
            })(l.padding, i);
            const g = B(h);
            const _ = f === 'y' ? s : a;
            const b = f === 'y' ? o : r;
            const v =
                  i.rects.reference[p] +
                  i.rects.reference[f] -
                  u[f] -
                  i.rects.popper[p];
            const y = u[f] - i.rects.reference[f];
            const w = X(h);
            const A = w ?
              f === 'y' ?
                w.clientHeight || 0 :
                w.clientWidth || 0 :
              0;
            const E = v / 2 - y / 2;
            const C = m[_];
            const O = A - g[p] - m[b];
            const T = A / 2 - g[p] / 2 + E;
            const x = U(C, T, O);
            const k = f;
            i.modifiersData[n] =
                (((e = {})[k] = x), (e.centerOffset = x - T), e);
          }
        },
        effect: function(t) {
          const e = t.state;
          const i = t.options.element;
          let n = void 0 === i ? '[data-popper-arrow]' : i;
          n != null &&
              (typeof n !== 'string' ||
                (n = e.elements.popper.querySelector(n))) &&
              z(e.elements.popper, n) &&
              (e.elements.arrow = n);
        },
        requires: ['popperOffsets'],
        requiresIfExists: ['preventOverflow'],
      };
      function tt(t) {
        return t.split('-')[1];
      }
      const et = { top: 'auto', right: 'auto', bottom: 'auto', left: 'auto' };
      function it(t) {
        let e;
        const i = t.popper;
        const n = t.popperRect;
        const l = t.placement;
        const c = t.variation;
        const h = t.offsets;
        const d = t.position;
        const f = t.gpuAcceleration;
        const p = t.adaptive;
        const m = t.roundOffsets;
        const g = t.isFixed;
        const _ = h.x;
        let b = void 0 === _ ? 0 : _;
        const v = h.y;
        let y = void 0 === v ? 0 : v;
        const w = typeof m === 'function' ? m({ x: b, y }) : { x: b, y };
        ((b = w.x), (y = w.y));
        const A = h.hasOwnProperty('x');
        const E = h.hasOwnProperty('y');
        let C = a;
        let O = s;
        const T = window;
        if (p) {
          let x = X(i);
          let k = 'clientHeight';
          let S = 'clientWidth';
          if (
            (x === L(i) &&
                R((x = V(i))).position !== 'static' &&
                d === 'absolute' &&
                ((k = 'scrollHeight'), (S = 'scrollWidth')),
            l === s || ((l === a || l === r) && c === u))
          ) {
            ((O = o),
            (y -=
                  (g && x === T && T.visualViewport ?
                    T.visualViewport.height :
                    x[k]) - n.height),
            (y *= f ? 1 : -1));
          }
          if (l === a || ((l === s || l === o) && c === u)) {
            ((C = r),
            (b -=
                  (g && x === T && T.visualViewport ?
                    T.visualViewport.width :
                    x[S]) - n.width),
            (b *= f ? 1 : -1));
          }
        }
        let D;
        const $ = Object.assign({ position: d }, p && et);
        const I =
              !0 === m ?
                (function(t, e) {
                  const i = t.x;
                  const n = t.y;
                  const s = e.devicePixelRatio || 1;
                  return { x: M(i * s) / s || 0, y: M(n * s) / s || 0 };
                })({ x: b, y }, L(i)) :
                { x: b, y };
        return (
          (b = I.x),
          (y = I.y),
          f ?
            Object.assign(
              {},
              $,
              (((D = {})[O] = E ? '0' : ''),
              (D[C] = A ? '0' : ''),
              (D.transform =
                    (T.devicePixelRatio || 1) <= 1 ?
                      `translate(${b}px, ${y}px)` :
                      `translate3d(${b}px, ${y}px, 0)`),
              D),
            ) :
            Object.assign(
              {},
              $,
              (((e = {})[O] = E ? `${y}px` : ''),
              (e[C] = A ? `${b}px` : ''),
              (e.transform = ''),
              e),
            )
        );
      }
      const nt = {
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
            placement: N(e.placement),
            variation: tt(e.placement),
            popper: e.elements.popper,
            popperRect: e.rects.popper,
            gpuAcceleration: s,
            isFixed: e.options.strategy === 'fixed',
          };
          (e.modifiersData.popperOffsets != null &&
              (e.styles.popper = Object.assign(
                {},
                e.styles.popper,
                it(
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
                  it(
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
      const st = { passive: !0 };
      const ot = {
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
          const l = L(e.elements.popper);
          const c = [].concat(e.scrollParents.reference, e.scrollParents.popper);
          return (
            o &&
                c.forEach(t => {
                  t.addEventListener('scroll', i.update, st);
                }),
            a && l.addEventListener('resize', i.update, st),
            function() {
              (o &&
                  c.forEach(t => {
                    t.removeEventListener('scroll', i.update, st);
                  }),
              a && l.removeEventListener('resize', i.update, st));
            }
          );
        },
        data: {},
      };
      const rt = { left: 'right', right: 'left', bottom: 'top', top: 'bottom' };
      function at(t) {
        return t.replace(/left|right|bottom|top/g, t => rt[t]);
      }
      const lt = { start: 'end', end: 'start' };
      function ct(t) {
        return t.replace(/start|end/g, t => lt[t]);
      }
      function ht(t) {
        const e = L(t);
        return { scrollLeft: e.pageXOffset, scrollTop: e.pageYOffset };
      }
      function ut(t) {
        return W(V(t)).left + ht(t).scrollLeft;
      }
      function dt(t) {
        const e = R(t);
        const i = e.overflow;
        const n = e.overflowX;
        const s = e.overflowY;
        return /auto|scroll|overlay|hidden/.test(i + s + n);
      }
      function ft(t) {
        return ['html', 'body', '#document'].indexOf(k(t)) >= 0 ?
          t.ownerDocument.body :
          D(t) && dt(t) ?
            t :
            ft(K(t));
      }
      function pt(t, e) {
        let i;
        void 0 === e && (e = []);
        const n = ft(t);
        const s = n === ((i = t.ownerDocument) == null ? void 0 : i.body);
        const o = L(n);
        const r = s ? [o].concat(o.visualViewport || [], dt(n) ? n : []) : n;
        const a = e.concat(r);
        return s ? a : a.concat(pt(K(r)));
      }
      function mt(t) {
        return Object.assign({}, t, {
          left: t.x,
          top: t.y,
          right: t.x + t.width,
          bottom: t.y + t.height,
        });
      }
      function gt(t, e, i) {
        return e === f ?
          mt(
            (function(t, e) {
              const i = L(t);
              const n = V(t);
              const s = i.visualViewport;
              let o = n.clientWidth;
              let r = n.clientHeight;
              let a = 0;
              let l = 0;
              if (s) {
                ((o = s.width), (r = s.height));
                const c = H();
                (c || (!c && e === 'fixed')) &&
                      ((a = s.offsetLeft), (l = s.offsetTop));
              }
              return { width: o, height: r, x: a + ut(t), y: l };
            })(t, i),
          ) :
          S(e) ?
            (function(t, e) {
              const i = W(t, !1, e === 'fixed');
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
            mt(
              (function(t) {
                let e;
                const i = V(t);
                const n = ht(t);
                const s = (e = t.ownerDocument) == null ? void 0 : e.body;
                const o = P(
                  i.scrollWidth,
                  i.clientWidth,
                  s ? s.scrollWidth : 0,
                  s ? s.clientWidth : 0,
                );
                const r = P(
                  i.scrollHeight,
                  i.clientHeight,
                  s ? s.scrollHeight : 0,
                  s ? s.clientHeight : 0,
                );
                let a = -n.scrollLeft + ut(t);
                const l = -n.scrollTop;
                return (
                  R(s || i).direction === 'rtl' &&
                        (a += P(i.clientWidth, s ? s.clientWidth : 0) - o),
                  { width: o, height: r, x: a, y: l }
                );
              })(V(t)),
            );
      }
      function _t(t, e, i, n) {
        const s =
              e === 'clippingParents' ?
                (function(t) {
                  const e = pt(K(t));
                  const i =
                        ['absolute', 'fixed'].indexOf(R(t).position) >= 0 &&
                        D(t) ?
                          X(t) :
                          t;
                  return S(i) ?
                    e.filter(t => S(t) && z(t, i) && k(t) !== 'body') :
                    [];
                })(t) :
                [].concat(e);
        const o = [].concat(s, [i]);
        const r = o[0];
        const a = o.reduce(
          (e, i) => {
            const s = gt(t, i, n);
            return (
              (e.top = P(s.top, e.top)),
              (e.right = j(s.right, e.right)),
              (e.bottom = j(s.bottom, e.bottom)),
              (e.left = P(s.left, e.left)),
              e
            );
          },
          gt(t, r, n),
        );
        return (
          (a.width = a.right - a.left),
          (a.height = a.bottom - a.top),
          (a.x = a.left),
          (a.y = a.top),
          a
        );
      }
      function bt(t) {
        let e;
        const i = t.reference;
        const n = t.element;
        const l = t.placement;
        const c = l ? N(l) : null;
        const d = l ? tt(l) : null;
        const f = i.x + i.width / 2 - n.width / 2;
        const p = i.y + i.height / 2 - n.height / 2;
        switch (c) {
        case s:
          e = { x: f, y: i.y - n.height };
          break;
        case o:
          e = { x: f, y: i.y + i.height };
          break;
        case r:
          e = { x: i.x + i.width, y: p };
          break;
        case a:
          e = { x: i.x - n.width, y: p };
          break;
        default:
          e = { x: i.x, y: i.y };
        }
        const m = c ? Y(c) : null;
        if (m != null) {
          const g = m === 'y' ? 'height' : 'width';
          switch (d) {
          case h:
            e[m] = e[m] - (i[g] / 2 - n[g] / 2);
            break;
          case u:
            e[m] = e[m] + (i[g] / 2 - n[g] / 2);
          }
        }
        return e;
      }
      function vt(t, e) {
        void 0 === e && (e = {});
        const i = e;
        const n = i.placement;
        const a = void 0 === n ? t.placement : n;
        const l = i.strategy;
        const h = void 0 === l ? t.strategy : l;
        const u = i.boundary;
        const g = void 0 === u ? d : u;
        const _ = i.rootBoundary;
        const b = void 0 === _ ? f : _;
        const v = i.elementContext;
        const y = void 0 === v ? p : v;
        const w = i.altBoundary;
        const A = void 0 !== w && w;
        const E = i.padding;
        const C = void 0 === E ? 0 : E;
        const O = G(typeof C !== 'number' ? C : J(C, c));
        const T = y === p ? m : p;
        const x = t.rects.popper;
        const k = t.elements[A ? T : y];
        const L = _t(
          S(k) ? k : k.contextElement || V(t.elements.popper),
          g,
          b,
          h,
        );
        const D = W(t.elements.reference);
        const $ = bt({
          reference: D,
          element: x,
          strategy: 'absolute',
          placement: a,
        });
        const I = mt(Object.assign({}, x, $));
        const N = y === p ? I : D;
        const P = {
          top: L.top - N.top + O.top,
          bottom: N.bottom - L.bottom + O.bottom,
          left: L.left - N.left + O.left,
          right: N.right - L.right + O.right,
        };
        const j = t.modifiersData.offset;
        if (y === p && j) {
          const M = j[a];
          Object.keys(P).forEach(t => {
            const e = [r, o].indexOf(t) >= 0 ? 1 : -1;
            const i = [s, o].indexOf(t) >= 0 ? 'y' : 'x';
            P[t] += M[i] * e;
          });
        }
        return P;
      }
      const yt = {
        name: 'flip',
        enabled: !0,
        phase: 'main',
        fn: function(t) {
          const e = t.state;
          const i = t.options;
          const n = t.name;
          if (!e.modifiersData[n]._skip) {
            for (
              var u = i.mainAxis,
                d = void 0 === u || u,
                f = i.altAxis,
                p = void 0 === f || f,
                m = i.fallbackPlacements,
                b = i.padding,
                v = i.boundary,
                y = i.rootBoundary,
                w = i.altBoundary,
                A = i.flipVariations,
                E = void 0 === A || A,
                C = i.allowedAutoPlacements,
                O = e.options.placement,
                T = N(O),
                x =
                    m ||
                    (T === O || !E ?
                      [at(O)] :
                      (function(t) {
                        if (N(t) === l) {
                          return [];
                        }
                        const e = at(t);
                        return [ct(t), e, ct(e)];
                      })(O)),
                k = [O].concat(x).reduce((t, i) => t.concat(
                  N(i) === l ?
                    (function(t, e) {
                      void 0 === e && (e = {});
                      const i = e;
                      const n = i.placement;
                      const s = i.boundary;
                      const o = i.rootBoundary;
                      const r = i.padding;
                      const a = i.flipVariations;
                      const l = i.allowedAutoPlacements;
                      const h = void 0 === l ? _ : l;
                      const u = tt(n);
                      const d = u ?
                        a ?
                          g :
                          g.filter(t => tt(t) === u) :
                        c;
                      let f = d.filter(t => h.indexOf(t) >= 0);
                      f.length === 0 && (f = d);
                      const p = f.reduce((e, i) => (
                        (e[i] = vt(t, {
                          placement: i,
                          boundary: s,
                          rootBoundary: o,
                          padding: r,
                        })[N(i)]),
                        e
                      ), {});
                      return Object.keys(p).sort((t, e) => p[t] - p[e]);
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
                $ = !0,
                I = k[0],
                P = 0;
              P < k.length;
              P++
            ) {
              const j = k[P];
              const M = N(j);
              const F = tt(j) === h;
              const H = [s, o].indexOf(M) >= 0;
              const W = H ? 'width' : 'height';
              const B = vt(e, {
                placement: j,
                boundary: v,
                rootBoundary: y,
                altBoundary: w,
                padding: b,
              });
              let z = H ? (F ? r : a) : F ? o : s;
              L[W] > S[W] && (z = at(z));
              const R = at(z);
              const q = [];
              if (
                (d && q.push(B[M] <= 0),
                p && q.push(B[z] <= 0, B[R] <= 0),
                q.every(t => t))
              ) {
                ((I = j), ($ = !1));
                break;
              }
              D.set(j, q);
            }
            if ($) {
              for (
                let V = function(t) {
                    const e = k.find(e => {
                      const i = D.get(e);
                      if (i) {
                        return i.slice(0, t).every(t => t);
                      }
                    });
                    if (e) {
                      return ((I = e), 'break');
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
            e.placement !== I &&
                ((e.modifiersData[n]._skip = !0),
                (e.placement = I),
                (e.reset = !0));
          }
        },
        requiresIfExists: ['offset'],
        data: { _skip: !1 },
      };
      function wt(t, e, i) {
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
      function At(t) {
        return [s, r, o, a].some(e => t[e] >= 0);
      }
      const Et = {
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
          const r = vt(e, { elementContext: 'reference' });
          const a = vt(e, { altBoundary: !0 });
          const l = wt(r, n);
          const c = wt(a, s, o);
          const h = At(l);
          const u = At(c);
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
      const Ct = {
        name: 'offset',
        enabled: !0,
        phase: 'main',
        requires: ['popperOffsets'],
        fn: function(t) {
          const e = t.state;
          const i = t.options;
          const n = t.name;
          const o = i.offset;
          const l = void 0 === o ? [0, 0] : o;
          const c = _.reduce((t, i) => (
            (t[i] = (function(t, e, i) {
              const n = N(t);
              const o = [a, s].indexOf(n) >= 0 ? -1 : 1;
              const l =
                        typeof i === 'function' ?
                          i(Object.assign({}, e, { placement: t })) :
                          i;
              let c = l[0];
              let h = l[1];
              return (
                (c = c || 0),
                (h = (h || 0) * o),
                [a, r].indexOf(n) >= 0 ? { x: h, y: c } : { x: c, y: h }
              );
            })(i, e.rects, l)),
            t
          ), {});
          const h = c[e.placement];
          const u = h.x;
          const d = h.y;
          (e.modifiersData.popperOffsets != null &&
              ((e.modifiersData.popperOffsets.x += u),
              (e.modifiersData.popperOffsets.y += d)),
          (e.modifiersData[n] = c));
        },
      };
      const Ot = {
        name: 'popperOffsets',
        enabled: !0,
        phase: 'read',
        fn: function(t) {
          const e = t.state;
          const i = t.name;
          e.modifiersData[i] = bt({
            reference: e.rects.reference,
            element: e.rects.popper,
            strategy: 'absolute',
            placement: e.placement,
          });
        },
        data: {},
      };
      const Tt = {
        name: 'preventOverflow',
        enabled: !0,
        phase: 'main',
        fn: function(t) {
          const e = t.state;
          const i = t.options;
          const n = t.name;
          const l = i.mainAxis;
          const c = void 0 === l || l;
          const u = i.altAxis;
          const d = void 0 !== u && u;
          const f = i.boundary;
          const p = i.rootBoundary;
          const m = i.altBoundary;
          const g = i.padding;
          const _ = i.tether;
          const b = void 0 === _ || _;
          const v = i.tetherOffset;
          const y = void 0 === v ? 0 : v;
          const w = vt(e, {
            boundary: f,
            rootBoundary: p,
            padding: g,
            altBoundary: m,
          });
          const A = N(e.placement);
          const E = tt(e.placement);
          const C = !E;
          const O = Y(A);
          const T = O === 'x' ? 'y' : 'x';
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
          const $ = e.modifiersData.offset ?
            e.modifiersData.offset[e.placement] :
            null;
          const I = { x: 0, y: 0 };
          if (x) {
            if (c) {
              let M;
              const F = O === 'y' ? s : a;
              const H = O === 'y' ? o : r;
              const W = O === 'y' ? 'height' : 'width';
              const z = x[O];
              const R = z + w[F];
              const q = z - w[H];
              const V = b ? -L[W] / 2 : 0;
              const K = E === h ? k[W] : L[W];
              const Q = E === h ? -L[W] : -k[W];
              const G = e.elements.arrow;
              const J = b && G ? B(G) : { width: 0, height: 0 };
              const Z = e.modifiersData['arrow#persistent'] ?
                e.modifiersData['arrow#persistent'].padding :
                { top: 0, right: 0, bottom: 0, left: 0 };
              const et = Z[F];
              const it = Z[H];
              const nt = U(0, k[W], J[W]);
              const st = C ?
                k[W] / 2 - V - nt - et - D.mainAxis :
                K - nt - et - D.mainAxis;
              const ot = C ?
                -k[W] / 2 + V + nt + it + D.mainAxis :
                Q + nt + it + D.mainAxis;
              const rt = e.elements.arrow && X(e.elements.arrow);
              const at = rt ?
                O === 'y' ?
                  rt.clientTop || 0 :
                  rt.clientLeft || 0 :
                0;
              const lt = (M = $ == null ? void 0 : $[O]) != null ? M : 0;
              const ct = z + ot - lt;
              const ht = U(b ? j(R, z + st - lt - at) : R, z, b ? P(q, ct) : q);
              ((x[O] = ht), (I[O] = ht - z));
            }
            if (d) {
              let ut;
              const dt = O === 'x' ? s : a;
              const ft = O === 'x' ? o : r;
              const pt = x[T];
              const mt = T === 'y' ? 'height' : 'width';
              const gt = pt + w[dt];
              const _t = pt - w[ft];
              const bt = [s, a].indexOf(A) !== -1;
              const yt = (ut = $ == null ? void 0 : $[T]) != null ? ut : 0;
              const wt = bt ? gt : pt - k[mt] - L[mt] - yt + D.altAxis;
              const At = bt ? pt + k[mt] + L[mt] - yt - D.altAxis : _t;
              const Et =
                    b && bt ?
                      (function(t, e, i) {
                        const n = U(t, e, i);
                        return n > i ? i : n;
                      })(wt, pt, At) :
                      U(b ? wt : gt, pt, b ? At : _t);
              ((x[T] = Et), (I[T] = Et - pt));
            }
            e.modifiersData[n] = I;
          }
        },
        requiresIfExists: ['offset'],
      };
      function xt(t, e, i) {
        void 0 === i && (i = !1);
        let n;
        let s;
        const o = D(e);
        const r =
              D(e) &&
              (function(t) {
                const e = t.getBoundingClientRect();
                const i = M(e.width) / t.offsetWidth || 1;
                const n = M(e.height) / t.offsetHeight || 1;
                return i !== 1 || n !== 1;
              })(e);
        const a = V(e);
        const l = W(t, r, i);
        let c = { scrollLeft: 0, scrollTop: 0 };
        let h = { x: 0, y: 0 };
        return (
          (o || (!o && !i)) &&
              ((k(e) !== 'body' || dt(a)) &&
                (c =
                  (n = e) !== L(n) && D(n) ?
                    { scrollLeft: (s = n).scrollLeft, scrollTop: s.scrollTop } :
                    ht(n)),
              D(e) ?
                (((h = W(e, !0)).x += e.clientLeft), (h.y += e.clientTop)) :
                a && (h.x = ut(a))),
          {
            x: l.left + c.scrollLeft - h.x,
            y: l.top + c.scrollTop - h.y,
            width: l.width,
            height: l.height,
          }
        );
      }
      function kt(t) {
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
      const Lt = { placement: 'bottom', modifiers: [], strategy: 'absolute' };
      function St() {
        for (var t = arguments.length, e = new Array(t), i = 0; i < t; i++) {
          e[i] = arguments[i];
        }
        return !e.some(t => !(t && typeof t.getBoundingClientRect === 'function'));
      }
      function Dt(t) {
        void 0 === t && (t = {});
        const e = t;
        const i = e.defaultModifiers;
        const n = void 0 === i ? [] : i;
        const s = e.defaultOptions;
        const o = void 0 === s ? Lt : s;
        return function(t, e, i) {
          void 0 === i && (i = o);
          let s;
          let r;
          let a = {
            placement: 'bottom',
            orderedModifiers: [],
            options: Object.assign({}, Lt, o),
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
                reference: S(t) ?
                  pt(t) :
                  t.contextElement ?
                    pt(t.contextElement) :
                    [],
                popper: pt(e),
              }));
              let r;
              let c;
              const d = (function(t) {
                const e = kt(t);
                return x.reduce((t, i) => t.concat(
                  e.filter(t => t.phase === i),
                ), []);
              })(
                ((r = [].concat(n, a.options.modifiers)),
                (c = r.reduce((t, e) => {
                  const i = t[e.name];
                  return (
                    (t[e.name] = i ?
                      Object.assign({}, i, e, {
                        options: Object.assign(
                          {},
                          i.options,
                          e.options,
                        ),
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
                    const o = s({
                      state: a,
                      name: e,
                      instance: h,
                      options: n,
                    });
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
                if (St(e, i)) {
                  ((a.rects = {
                    reference: xt(e, X(i), a.options.strategy === 'fixed'),
                    popper: B(i),
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
                              o({
                                state: a,
                                options: l,
                                name: u,
                                instance: h,
                              }) || a);
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
          if (!St(t, e)) {
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
      var $t = Dt();
      var It = Dt({ defaultModifiers: [ot, Ot, nt, I, Ct, yt, Tt, Z, Et] });
      var Nt = Dt({ defaultModifiers: [ot, Ot, nt, I] });
      const Pt = new Map();
      const jt = {
        set(t, e, i) {
          Pt.has(t) || Pt.set(t, new Map());
          const n = Pt.get(t);
          (n.has(e) || n.size === 0) && n.set(e, i);
        },
        get: (t, e) => (Pt.has(t) && Pt.get(t).get(e)) || null,
        remove(t, e) {
          if (!Pt.has(t)) {
            return;
          }
          const i = Pt.get(t);
          (i.delete(e), i.size === 0 && Pt.delete(t));
        },
      };
      const Mt = 'transitionend';
      const Ft = t => (
        t &&
              window.CSS &&
              window.CSS.escape &&
              (t = t.replace(/#([^\s"#']+)/g, (t, e) => `#${CSS.escape(e)}`)),
        t
      );
      const Ht = t =>
        t == null ?
          `${t}` :
          Object.prototype.toString
            .call(t)
            .match(/\s([a-z]+)/i)[1]
            .toLowerCase();
      const Wt = t => {
        t.dispatchEvent(new Event(Mt));
      };
      const Bt = t =>
        !(!t || typeof t !== 'object') &&
            (void 0 !== t.jquery && (t = t[0]), void 0 !== t.nodeType);
      const zt = t =>
        Bt(t) ?
          t.jquery ?
            t[0] :
            t :
          typeof t === 'string' && t.length > 0 ?
            document.querySelector(Ft(t)) :
            null;
      const Rt = t => {
        if (!Bt(t) || t.getClientRects().length === 0) {
          return !1;
        }
        const e =
                getComputedStyle(t).getPropertyValue('visibility') ===
                'visible';
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
      const qt = t =>
        !t ||
            t.nodeType !== Node.ELEMENT_NODE ||
            !!t.classList.contains('disabled') ||
            (void 0 !== t.disabled ?
              t.disabled :
              t.hasAttribute('disabled') &&
                t.getAttribute('disabled') !== 'false');
      const Vt = t => {
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
            Vt(t.parentNode) :
            null;
      };
      const Kt = () => {};
      const Qt = t => {
        t.offsetHeight;
      };
      const Xt = () =>
        window.jQuery && !document.body.hasAttribute('data-bs-no-jquery') ?
          window.jQuery :
          null;
      const Yt = [];
      const Ut = () => document.documentElement.dir === 'rtl';
      const Gt = t => {
        let e;
        ((e = () => {
          const e = Xt();
          if (e) {
            const i = t.NAME;
            const n = e.fn[i];
            ((e.fn[i] = t.jQueryInterface),
            (e.fn[i].Constructor = t),
            (e.fn[i].noConflict = () => (
              (e.fn[i] = n),
              t.jQueryInterface
            )));
          }
        }),
        document.readyState === 'loading' ?
          (Yt.length ||
                    document.addEventListener('DOMContentLoaded', () => {
                      for (const t of Yt) {
                        t();
                      }
                    }),
          Yt.push(e)) :
          e());
      };
      const Jt = (t, e = [], i = t) =>
        typeof t === 'function' ? t.call(...e) : i;
      const Zt = (t, e, i = !0) => {
        if (!i) {
          return void Jt(t);
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
          i === e && ((s = !0), e.removeEventListener(Mt, o), Jt(t));
        };
        (e.addEventListener(Mt, o),
        setTimeout(() => {
          s || Wt(e);
        }, n));
      };
      const te = (t, e, i, n) => {
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
      const ee = /[^.]*(?=\..*)\.|.*/;
      const ie = /\..*/;
      const ne = /::\d+$/;
      const se = {};
      let oe = 1;
      const re = { mouseenter: 'mouseover', mouseleave: 'mouseout' };
      const ae = new Set([
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
      function le(t, e) {
        return (e && `${e}::${oe++}`) || t.uidEvent || oe++;
      }
      function ce(t) {
        const e = le(t);
        return ((t.uidEvent = e), (se[e] = se[e] || {}), se[e]);
      }
      function he(t, e, i = null) {
        return Object.values(t).find(
          t => t.callable === e && t.delegationSelector === i,
        );
      }
      function ue(t, e, i) {
        const n = typeof e === 'string';
        const s = n ? i : e || i;
        let o = me(t);
        return (ae.has(o) || (o = t), [n, s, o]);
      }
      function de(t, e, i, n, s) {
        if (typeof e !== 'string' || !t) {
          return;
        }
        let [o, r, a] = ue(e, i, n);
        if (e in re) {
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
        const l = ce(t);
        const c = l[a] || (l[a] = {});
        const h = he(c, r, o ? i : null);
        if (h) {
          return void (h.oneOff = h.oneOff && s);
        }
        const u = le(r, e.replace(ee, ''));
        const d = o ?
          (function(t, e, i) {
            return function n(s) {
              const o = t.querySelectorAll(e);
              for (
                let { target: r } = s;
                r && r !== this;
                r = r.parentNode
              ) {
                for (const a of o) {
                  if (a === r) {
                    return (
                      _e(s, { delegateTarget: r }),
                      n.oneOff && ge.off(t, s.type, e, i),
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
                _e(n, { delegateTarget: t }),
                i.oneOff && ge.off(t, n.type, e),
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
      function fe(t, e, i, n, s) {
        const o = he(e[i], n, s);
        o &&
            (t.removeEventListener(i, o, Boolean(s)), delete e[i][o.uidEvent]);
      }
      function pe(t, e, i, n) {
        const s = e[i] || {};
        for (const [o, r] of Object.entries(s)) {
          o.includes(n) && fe(t, e, i, r.callable, r.delegationSelector);
        }
      }
      function me(t) {
        return ((t = t.replace(ie, '')), re[t] || t);
      }
      const ge = {
        on(t, e, i, n) {
          de(t, e, i, n, !1);
        },
        one(t, e, i, n) {
          de(t, e, i, n, !0);
        },
        off(t, e, i, n) {
          if (typeof e !== 'string' || !t) {
            return;
          }
          const [s, o, r] = ue(e, i, n);
          const a = r !== e;
          const l = ce(t);
          const c = l[r] || {};
          const h = e.startsWith('.');
          if (void 0 === o) {
            if (h) {
              for (const i of Object.keys(l)) {
                pe(t, l, i, e.slice(1));
              }
            }
            for (const [i, n] of Object.entries(c)) {
              const s = i.replace(ne, '');
              (a && !e.includes(s)) ||
                  fe(t, l, r, n.callable, n.delegationSelector);
            }
          } else {
            if (!Object.keys(c).length) {
              return;
            }
            fe(t, l, r, o, s ? i : null);
          }
        },
        trigger(t, e, i) {
          if (typeof e !== 'string' || !t) {
            return null;
          }
          const n = Xt();
          let s = null;
          let o = !0;
          let r = !0;
          let a = !1;
          e !== me(e) &&
              n &&
              ((s = n.Event(e, i)),
              n(t).trigger(s),
              (o = !s.isPropagationStopped()),
              (r = !s.isImmediatePropagationStopped()),
              (a = s.isDefaultPrevented()));
          const l = _e(new Event(e, { bubbles: o, cancelable: !0 }), i);
          return (
            a && l.preventDefault(),
            r && t.dispatchEvent(l),
            l.defaultPrevented && s && s.preventDefault(),
            l
          );
        },
      };
      function _e(t, e = {}) {
        for (const [i, n] of Object.entries(e)) {
          try {
            t[i] = n;
          } catch (e) {
            Object.defineProperty(t, i, { configurable: !0, get: () => n });
          }
        }
        return t;
      }
      function be(t) {
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
      function ve(t) {
        return t.replace(/[A-Z]/g, t => `-${t.toLowerCase()}`);
      }
      const ye = {
        setDataAttribute(t, e, i) {
          t.setAttribute(`data-bs-${ve(e)}`, i);
        },
        removeDataAttribute(t, e) {
          t.removeAttribute(`data-bs-${ve(e)}`);
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
            (e[i] = be(t.dataset[n])));
          }
          return e;
        },
        getDataAttribute: (t, e) => be(t.getAttribute(`data-bs-${ve(e)}`)),
      };
      class we {
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
          const i = Bt(e) ? ye.getDataAttribute(e, 'config') : {};
          return {
            ...this.constructor.Default,
            ...(typeof i === 'object' ? i : {}),
            ...(Bt(e) ? ye.getDataAttributes(e) : {}),
            ...(typeof t === 'object' ? t : {}),
          };
        }

        _typeCheckConfig(t, e = this.constructor.DefaultType) {
          for (const [i, n] of Object.entries(e)) {
            const e = t[i];
            const s = Bt(e) ? 'element' : Ht(e);
            if (!new RegExp(n).test(s)) {
              throw new TypeError(
                `${this.constructor.NAME.toUpperCase()}: Option "${i}" provided type "${s}" but expected type "${n}".`,
              );
            }
          }
        }
      }
      class Ae extends we {
        constructor(t, e) {
          (super(),
          (t = zt(t)) &&
                ((this._element = t),
                (this._config = this._getConfig(e)),
                jt.set(this._element, this.constructor.DATA_KEY, this)));
        }

        dispose() {
          (jt.remove(this._element, this.constructor.DATA_KEY),
          ge.off(this._element, this.constructor.EVENT_KEY));
          for (const t of Object.getOwnPropertyNames(this)) {
            this[t] = null;
          }
        }

        _queueCallback(t, e, i = !0) {
          Zt(t, e, i);
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
          return jt.get(zt(t), this.DATA_KEY);
        }

        static getOrCreateInstance(t, e = {}) {
          return (
            this.getInstance(t) ||
              new this(t, typeof e === 'object' ? e : null)
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
      const Ee = t => {
        let e = t.getAttribute('data-bs-target');
        if (!e || e === '#') {
          let i = t.getAttribute('href');
          if (!i || (!i.includes('#') && !i.startsWith('.'))) {
            return null;
          }
          (i.includes('#') &&
                !i.startsWith('#') &&
                (i = `#${i.split('#')[1]}`),
          (e = i && i !== '#' ? i.trim() : null));
        }
        return e ?
          e
            .split(',')
            .map(t => Ft(t))
            .join(',') :
          null;
      };
      const Ce = {
        find: (t, e = document.documentElement) =>
          [].concat(...Element.prototype.querySelectorAll.call(e, t)),
        findOne: (t, e = document.documentElement) =>
          Element.prototype.querySelector.call(e, t),
        children: (t, e) =>
          [].concat(...t.children).filter(t => t.matches(e)),
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
          return this.find(e, t).filter(t => !qt(t) && Rt(t));
        },
        getSelectorFromElement(t) {
          const e = Ee(t);
          return e && Ce.findOne(e) ? e : null;
        },
        getElementFromSelector(t) {
          const e = Ee(t);
          return e ? Ce.findOne(e) : null;
        },
        getMultipleElementsFromSelector(t) {
          const e = Ee(t);
          return e ? Ce.find(e) : [];
        },
      };
      const Oe = (t, e = 'hide') => {
        const i = `click.dismiss${t.EVENT_KEY}`;
        const n = t.NAME;
        ge.on(document, i, `[data-bs-dismiss="${n}"]`, function(i) {
          if (
            (['A', 'AREA'].includes(this.tagName) && i.preventDefault(),
            qt(this))
          ) {
            return;
          }
          const s =
                Ce.getElementFromSelector(this) || this.closest(`.${n}`);
          t.getOrCreateInstance(s)[e]();
        });
      };
      const Te = '.bs.alert';
      const xe = `close${Te}`;
      const ke = `closed${Te}`;
      class Le extends Ae {
        static get NAME() {
          return 'alert';
        }

        close() {
          if (ge.trigger(this._element, xe).defaultPrevented) {
            return;
          }
          this._element.classList.remove('show');
          const t = this._element.classList.contains('fade');
          this._queueCallback(() => this._destroyElement(), this._element, t);
        }

        _destroyElement() {
          (this._element.remove(),
          ge.trigger(this._element, ke),
          this.dispose());
        }

        static jQueryInterface(t) {
          return this.each(function() {
            const e = Le.getOrCreateInstance(this);
            if (typeof t === 'string') {
              if (void 0 === e[t] || t.startsWith('_') || t === 'constructor') {
                throw new TypeError(`No method named "${t}"`);
              }
              e[t](this);
            }
          });
        }
      }
      (Oe(Le, 'close'), Gt(Le));
      const Se = '[data-bs-toggle="button"]';
      class De extends Ae {
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
            const e = De.getOrCreateInstance(this);
            t === 'toggle' && e[t]();
          });
        }
      }
      (ge.on(document, 'click.bs.button.data-api', Se, t => {
        t.preventDefault();
        const e = t.target.closest(Se);
        De.getOrCreateInstance(e).toggle();
      }),
      Gt(De));
      const $e = '.bs.swipe';
      const Ie = `touchstart${$e}`;
      const Ne = `touchmove${$e}`;
      const Pe = `touchend${$e}`;
      const je = `pointerdown${$e}`;
      const Me = `pointerup${$e}`;
      const Fe = { endCallback: null, leftCallback: null, rightCallback: null };
      const He = {
        endCallback: '(function|null)',
        leftCallback: '(function|null)',
        rightCallback: '(function|null)',
      };
      class We extends we {
        constructor(t, e) {
          (super(),
          (this._element = t),
          t &&
                We.isSupported() &&
                ((this._config = this._getConfig(e)),
                (this._deltaX = 0),
                (this._supportPointerEvents = Boolean(window.PointerEvent)),
                this._initEvents()));
        }

        static get Default() {
          return Fe;
        }

        static get DefaultType() {
          return He;
        }

        static get NAME() {
          return 'swipe';
        }

        dispose() {
          ge.off(this._element, $e);
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
          Jt(this._config.endCallback));
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
                Jt(
                  e > 0 ?
                    this._config.rightCallback :
                    this._config.leftCallback,
                ));
        }

        _initEvents() {
          this._supportPointerEvents ?
            (ge.on(this._element, je, t => this._start(t)),
            ge.on(this._element, Me, t => this._end(t)),
            this._element.classList.add('pointer-event')) :
            (ge.on(this._element, Ie, t => this._start(t)),
            ge.on(this._element, Ne, t => this._move(t)),
            ge.on(this._element, Pe, t => this._end(t)));
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
      const Be = '.bs.carousel';
      const ze = '.data-api';
      const Re = 'ArrowLeft';
      const qe = 'ArrowRight';
      const Ve = 'next';
      const Ke = 'prev';
      const Qe = 'left';
      const Xe = 'right';
      const Ye = `slide${Be}`;
      const Ue = `slid${Be}`;
      const Ge = `keydown${Be}`;
      const Je = `mouseenter${Be}`;
      const Ze = `mouseleave${Be}`;
      const ti = `dragstart${Be}`;
      const ei = `load${Be}${ze}`;
      const ii = `click${Be}${ze}`;
      const ni = 'carousel';
      const si = 'active';
      const oi = '.active';
      const ri = '.carousel-item';
      const ai = oi + ri;
      const li = { [Re]: Xe, [qe]: Qe };
      const ci = {
        interval: 5e3,
        keyboard: !0,
        pause: 'hover',
        ride: !1,
        touch: !0,
        wrap: !0,
      };
      const hi = {
        interval: '(number|boolean)',
        keyboard: 'boolean',
        pause: '(string|boolean)',
        ride: '(boolean|string)',
        touch: 'boolean',
        wrap: 'boolean',
      };
      class ui extends Ae {
        constructor(t, e) {
          (super(t, e),
          (this._interval = null),
          (this._activeElement = null),
          (this._isSliding = !1),
          (this.touchTimeout = null),
          (this._swipeHelper = null),
          (this._indicatorsElement = Ce.findOne(
            '.carousel-indicators',
            this._element,
          )),
          this._addEventListeners(),
          this._config.ride === ni && this.cycle());
        }

        static get Default() {
          return ci;
        }

        static get DefaultType() {
          return hi;
        }

        static get NAME() {
          return 'carousel';
        }

        next() {
          this._slide(Ve);
        }

        nextWhenVisible() {
          !document.hidden && Rt(this._element) && this.next();
        }

        prev() {
          this._slide(Ke);
        }

        pause() {
          (this._isSliding && Wt(this._element), this._clearInterval());
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
                ge.one(this._element, Ue, () => this.cycle()) :
                this.cycle());
        }

        to(t) {
          const e = this._getItems();
          if (t > e.length - 1 || t < 0) {
            return;
          }
          if (this._isSliding) {
            return void ge.one(this._element, Ue, () => this.to(t));
          }
          const i = this._getItemIndex(this._getActive());
          if (i === t) {
            return;
          }
          const n = t > i ? Ve : Ke;
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
              ge.on(this._element, Ge, t => this._keydown(t)),
          this._config.pause === 'hover' &&
                (ge.on(this._element, Je, () => this.pause()),
                ge.on(this._element, Ze, () => this._maybeEnableCycle())),
          this._config.touch &&
                We.isSupported() &&
                this._addTouchEventListeners());
        }

        _addTouchEventListeners() {
          for (const t of Ce.find('.carousel-item img', this._element)) {
            ge.on(t, ti, t => t.preventDefault());
          }
          const t = {
            leftCallback: () => this._slide(this._directionToOrder(Qe)),
            rightCallback: () => this._slide(this._directionToOrder(Xe)),
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
          this._swipeHelper = new We(this._element, t);
        }

        _keydown(t) {
          if (/input|textarea/i.test(t.target.tagName)) {
            return;
          }
          const e = li[t.key];
          e && (t.preventDefault(), this._slide(this._directionToOrder(e)));
        }

        _getItemIndex(t) {
          return this._getItems().indexOf(t);
        }

        _setActiveIndicatorElement(t) {
          if (!this._indicatorsElement) {
            return;
          }
          const e = Ce.findOne(oi, this._indicatorsElement);
          (e.classList.remove(si), e.removeAttribute('aria-current'));
          const i = Ce.findOne(
            `[data-bs-slide-to="${t}"]`,
            this._indicatorsElement,
          );
          i && (i.classList.add(si), i.setAttribute('aria-current', 'true'));
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
          const n = t === Ve;
          const s = e || te(this._getItems(), i, n, this._config.wrap);
          if (s === i) {
            return;
          }
          const o = this._getItemIndex(s);
          const r = e =>
            ge.trigger(this._element, e, {
              relatedTarget: s,
              direction: this._orderToDirection(t),
              from: this._getItemIndex(i),
              to: o,
            });
          if (r(Ye).defaultPrevented) {
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
          (s.classList.add(c), Qt(s), i.classList.add(l), s.classList.add(l));
          (this._queueCallback(
            () => {
              (s.classList.remove(l, c),
              s.classList.add(si),
              i.classList.remove(si, c, l),
              (this._isSliding = !1),
              r(Ue));
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
          return Ce.findOne(ai, this._element);
        }

        _getItems() {
          return Ce.find(ri, this._element);
        }

        _clearInterval() {
          this._interval &&
              (clearInterval(this._interval), (this._interval = null));
        }

        _directionToOrder(t) {
          return Ut() ? (t === Qe ? Ke : Ve) : t === Qe ? Ve : Ke;
        }

        _orderToDirection(t) {
          return Ut() ? (t === Ke ? Qe : Xe) : t === Ke ? Xe : Qe;
        }

        static jQueryInterface(t) {
          return this.each(function() {
            const e = ui.getOrCreateInstance(this, t);
            if (typeof t !== 'number') {
              if (typeof t === 'string') {
                if (
                  void 0 === e[t] ||
                    t.startsWith('_') ||
                    t === 'constructor'
                ) {
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
      (ge.on(
        document,
        ii,
        '[data-bs-slide], [data-bs-slide-to]',
        function(t) {
          const e = Ce.getElementFromSelector(this);
          if (!e || !e.classList.contains(ni)) {
            return;
          }
          t.preventDefault();
          const i = ui.getOrCreateInstance(e);
          const n = this.getAttribute('data-bs-slide-to');
          return n ?
            (i.to(n), void i._maybeEnableCycle()) :
            ye.getDataAttribute(this, 'slide') === 'next' ?
              (i.next(), void i._maybeEnableCycle()) :
              (i.prev(), void i._maybeEnableCycle());
        },
      ),
      ge.on(window, ei, () => {
        const t = Ce.find('[data-bs-ride="carousel"]');
        for (const e of t) {
          ui.getOrCreateInstance(e);
        }
      }),
      Gt(ui));
      const di = '.bs.collapse';
      const fi = `show${di}`;
      const pi = `shown${di}`;
      const mi = `hide${di}`;
      const gi = `hidden${di}`;
      const _i = `click${di}.data-api`;
      const bi = 'show';
      const vi = 'collapse';
      const yi = 'collapsing';
      const wi = `:scope .${vi} .${vi}`;
      const Ai = '[data-bs-toggle="collapse"]';
      const Ei = { parent: null, toggle: !0 };
      const Ci = { parent: '(null|element)', toggle: 'boolean' };
      class Oi extends Ae {
        constructor(t, e) {
          (super(t, e),
          (this._isTransitioning = !1),
          (this._triggerArray = []));
          const i = Ce.find(Ai);
          for (const t of i) {
            const e = Ce.getSelectorFromElement(t);
            const i = Ce.find(e).filter(t => t === this._element);
            e !== null && i.length && this._triggerArray.push(t);
          }
          (this._initializeChildren(),
          this._config.parent ||
                this._addAriaAndCollapsedClass(
                  this._triggerArray,
                  this._isShown(),
                ),
          this._config.toggle && this.toggle());
        }

        static get Default() {
          return Ei;
        }

        static get DefaultType() {
          return Ci;
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
                  .map(t => Oi.getOrCreateInstance(t, { toggle: !1 }))),
            t.length && t[0]._isTransitioning)
          ) {
            return;
          }
          if (ge.trigger(this._element, fi).defaultPrevented) {
            return;
          }
          for (const e of t) {
            e.hide();
          }
          const e = this._getDimension();
          (this._element.classList.remove(vi),
          this._element.classList.add(yi),
          (this._element.style[e] = 0),
          this._addAriaAndCollapsedClass(this._triggerArray, !0),
          (this._isTransitioning = !0));
          const i = `scroll${e[0].toUpperCase() + e.slice(1)}`;
          (this._queueCallback(
            () => {
              ((this._isTransitioning = !1),
              this._element.classList.remove(yi),
              this._element.classList.add(vi, bi),
              (this._element.style[e] = ''),
              ge.trigger(this._element, pi));
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
          if (ge.trigger(this._element, mi).defaultPrevented) {
            return;
          }
          const t = this._getDimension();
          ((this._element.style[t] =
              `${this._element.getBoundingClientRect()[t]}px`),
          Qt(this._element),
          this._element.classList.add(yi),
          this._element.classList.remove(vi, bi));
          for (const t of this._triggerArray) {
            const e = Ce.getElementFromSelector(t);
            e && !this._isShown(e) && this._addAriaAndCollapsedClass([t], !1);
          }
          this._isTransitioning = !0;
          ((this._element.style[t] = ''),
          this._queueCallback(
            () => {
              ((this._isTransitioning = !1),
              this._element.classList.remove(yi),
              this._element.classList.add(vi),
              ge.trigger(this._element, gi));
            },
            this._element,
            !0,
          ));
        }

        _isShown(t = this._element) {
          return t.classList.contains(bi);
        }

        _configAfterMerge(t) {
          return (
            (t.toggle = Boolean(t.toggle)),
            (t.parent = zt(t.parent)),
            t
          );
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
          const t = this._getFirstLevelChildren(Ai);
          for (const e of t) {
            const t = Ce.getElementFromSelector(e);
            t && this._addAriaAndCollapsedClass([e], this._isShown(t));
          }
        }

        _getFirstLevelChildren(t) {
          const e = Ce.find(wi, this._config.parent);
          return Ce.find(t, this._config.parent).filter(
            t => !e.includes(t),
          );
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
              const i = Oi.getOrCreateInstance(this, e);
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
      (ge.on(document, _i, Ai, function(t) {
        (t.target.tagName === 'A' ||
            (t.delegateTarget && t.delegateTarget.tagName === 'A')) &&
            t.preventDefault();
        for (const t of Ce.getMultipleElementsFromSelector(this)) {
          Oi.getOrCreateInstance(t, { toggle: !1 }).toggle();
        }
      }),
      Gt(Oi));
      const Ti = 'dropdown';
      const xi = '.bs.dropdown';
      const ki = '.data-api';
      const Li = 'ArrowUp';
      const Si = 'ArrowDown';
      const Di = `hide${xi}`;
      const $i = `hidden${xi}`;
      const Ii = `show${xi}`;
      const Ni = `shown${xi}`;
      const Pi = `click${xi}${ki}`;
      const ji = `keydown${xi}${ki}`;
      const Mi = `keyup${xi}${ki}`;
      const Fi = 'show';
      const Hi = '[data-bs-toggle="dropdown"]:not(.disabled):not(:disabled)';
      const Wi = `${Hi}.${Fi}`;
      const Bi = '.dropdown-menu';
      const zi = Ut() ? 'top-end' : 'top-start';
      const Ri = Ut() ? 'top-start' : 'top-end';
      const qi = Ut() ? 'bottom-end' : 'bottom-start';
      const Vi = Ut() ? 'bottom-start' : 'bottom-end';
      const Ki = Ut() ? 'left-start' : 'right-start';
      const Qi = Ut() ? 'right-start' : 'left-start';
      const Xi = {
        autoClose: !0,
        boundary: 'clippingParents',
        display: 'dynamic',
        offset: [0, 2],
        popperConfig: null,
        reference: 'toggle',
      };
      const Yi = {
        autoClose: '(boolean|string)',
        boundary: '(string|element)',
        display: 'string',
        offset: '(array|string|function)',
        popperConfig: '(null|object|function)',
        reference: '(string|element|object)',
      };
      class Ui extends Ae {
        constructor(t, e) {
          (super(t, e),
          (this._popper = null),
          (this._parent = this._element.parentNode),
          (this._menu =
                Ce.next(this._element, Bi)[0] ||
                Ce.prev(this._element, Bi)[0] ||
                Ce.findOne(Bi, this._parent)),
          (this._inNavbar = this._detectNavbar()));
        }

        static get Default() {
          return Xi;
        }

        static get DefaultType() {
          return Yi;
        }

        static get NAME() {
          return Ti;
        }

        toggle() {
          return this._isShown() ? this.hide() : this.show();
        }

        show() {
          if (qt(this._element) || this._isShown()) {
            return;
          }
          const t = { relatedTarget: this._element };
          if (!ge.trigger(this._element, Ii, t).defaultPrevented) {
            if (
              (this._createPopper(),
              'ontouchstart' in document.documentElement &&
                  !this._parent.closest('.navbar-nav'))
            ) {
              for (const t of [].concat(...document.body.children)) {
                ge.on(t, 'mouseover', Kt);
              }
            }
            (this._element.focus(),
            this._element.setAttribute('aria-expanded', !0),
            this._menu.classList.add(Fi),
            this._element.classList.add(Fi),
            ge.trigger(this._element, Ni, t));
          }
        }

        hide() {
          if (qt(this._element) || !this._isShown()) {
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
          if (!ge.trigger(this._element, Di, t).defaultPrevented) {
            if ('ontouchstart' in document.documentElement) {
              for (const t of [].concat(...document.body.children)) {
                ge.off(t, 'mouseover', Kt);
              }
            }
            (this._popper && this._popper.destroy(),
            this._menu.classList.remove(Fi),
            this._element.classList.remove(Fi),
            this._element.setAttribute('aria-expanded', 'false'),
            ye.removeDataAttribute(this._menu, 'popper'),
            ge.trigger(this._element, $i, t));
          }
        }

        _getConfig(t) {
          if (
            typeof (t = super._getConfig(t)).reference === 'object' &&
              !Bt(t.reference) &&
              typeof t.reference.getBoundingClientRect !== 'function'
          ) {
            throw new TypeError(
              `${Ti.toUpperCase()}: Option "reference" provided type "object" without a required "getBoundingClientRect" method.`,
            );
          }
          return t;
        }

        _createPopper() {
          let t = this._element;
          this._config.reference === 'parent' ?
            (t = this._parent) :
            Bt(this._config.reference) ?
              (t = zt(this._config.reference)) :
              typeof this._config.reference === 'object' &&
                  (t = this._config.reference);
          const e = this._getPopperConfig();
          this._popper = It(t, this._menu, e);
        }

        _isShown() {
          return this._menu.classList.contains(Fi);
        }

        _getPlacement() {
          const t = this._parent;
          if (t.classList.contains('dropend')) {
            return Ki;
          }
          if (t.classList.contains('dropstart')) {
            return Qi;
          }
          if (t.classList.contains('dropup-center')) {
            return 'top';
          }
          if (t.classList.contains('dropdown-center')) {
            return 'bottom';
          }
          const e =
              getComputedStyle(this._menu)
                .getPropertyValue('--bs-position')
                .trim() ===
              'end';
          return t.classList.contains('dropup') ? (e ? Ri : zi) : e ? Vi : qi;
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
                (ye.setDataAttribute(this._menu, 'popper', 'static'),
                (t.modifiers = [{ name: 'applyStyles', enabled: !1 }])),
            { ...t, ...Jt(this._config.popperConfig, [void 0, t]) }
          );
        }

        _selectMenuItem({ key: t, target: e }) {
          const i = Ce.find(
            '.dropdown-menu .dropdown-item:not(.disabled):not(:disabled)',
            this._menu,
          ).filter(t => Rt(t));
          i.length && te(i, e, t === Si, !i.includes(e)).focus();
        }

        static jQueryInterface(t) {
          return this.each(function() {
            const e = Ui.getOrCreateInstance(this, t);
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
          const e = Ce.find(Wi);
          for (const i of e) {
            const e = Ui.getInstance(i);
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
          const n = [Li, Si].includes(t.key);
          if (!n && !i) {
            return;
          }
          if (e && !i) {
            return;
          }
          t.preventDefault();
          const s = this.matches(Hi) ?
            this :
            Ce.prev(this, Hi)[0] ||
                  Ce.next(this, Hi)[0] ||
                  Ce.findOne(Hi, t.delegateTarget.parentNode);
          const o = Ui.getOrCreateInstance(s);
          if (n) {
            return (t.stopPropagation(), o.show(), void o._selectMenuItem(t));
          }
          o._isShown() && (t.stopPropagation(), o.hide(), s.focus());
        }
      }
      (ge.on(document, ji, Hi, Ui.dataApiKeydownHandler),
      ge.on(document, ji, Bi, Ui.dataApiKeydownHandler),
      ge.on(document, Pi, Ui.clearMenus),
      ge.on(document, Mi, Ui.clearMenus),
      ge.on(document, Pi, Hi, function(t) {
        (t.preventDefault(), Ui.getOrCreateInstance(this).toggle());
      }),
      Gt(Ui));
      const Gi = 'backdrop';
      const Ji = 'show';
      const Zi = `mousedown.bs.${Gi}`;
      const tn = {
        className: 'modal-backdrop',
        clickCallback: null,
        isAnimated: !1,
        isVisible: !0,
        rootElement: 'body',
      };
      const en = {
        className: 'string',
        clickCallback: '(function|null)',
        isAnimated: 'boolean',
        isVisible: 'boolean',
        rootElement: '(element|string)',
      };
      class nn extends we {
        constructor(t) {
          (super(),
          (this._config = this._getConfig(t)),
          (this._isAppended = !1),
          (this._element = null));
        }

        static get Default() {
          return tn;
        }

        static get DefaultType() {
          return en;
        }

        static get NAME() {
          return Gi;
        }

        show(t) {
          if (!this._config.isVisible) {
            return void Jt(t);
          }
          this._append();
          const e = this._getElement();
          (this._config.isAnimated && Qt(e),
          e.classList.add(Ji),
          this._emulateAnimation(() => {
            Jt(t);
          }));
        }

        hide(t) {
          this._config.isVisible ?
            (this._getElement().classList.remove(Ji),
            this._emulateAnimation(() => {
              (this.dispose(), Jt(t));
            })) :
            Jt(t);
        }

        dispose() {
          this._isAppended &&
              (ge.off(this._element, Zi),
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
          return ((t.rootElement = zt(t.rootElement)), t);
        }

        _append() {
          if (this._isAppended) {
            return;
          }
          const t = this._getElement();
          (this._config.rootElement.append(t),
          ge.on(t, Zi, () => {
            Jt(this._config.clickCallback);
          }),
          (this._isAppended = !0));
        }

        _emulateAnimation(t) {
          Zt(t, this._getElement(), this._config.isAnimated);
        }
      }
      const sn = '.bs.focustrap';
      const on = `focusin${sn}`;
      const rn = `keydown.tab${sn}`;
      const an = 'backward';
      const ln = { autofocus: !0, trapElement: null };
      const cn = { autofocus: 'boolean', trapElement: 'element' };
      class hn extends we {
        constructor(t) {
          (super(),
          (this._config = this._getConfig(t)),
          (this._isActive = !1),
          (this._lastTabNavDirection = null));
        }

        static get Default() {
          return ln;
        }

        static get DefaultType() {
          return cn;
        }

        static get NAME() {
          return 'focustrap';
        }

        activate() {
          this._isActive ||
              (this._config.autofocus && this._config.trapElement.focus(),
              ge.off(document, sn),
              ge.on(document, on, t => this._handleFocusin(t)),
              ge.on(document, rn, t => this._handleKeydown(t)),
              (this._isActive = !0));
        }

        deactivate() {
          this._isActive && ((this._isActive = !1), ge.off(document, sn));
        }

        _handleFocusin(t) {
          const { trapElement: e } = this._config;
          if (t.target === document || t.target === e || e.contains(t.target)) {
            return;
          }
          const i = Ce.focusableChildren(e);
          i.length === 0 ?
            e.focus() :
            this._lastTabNavDirection === an ?
              i[i.length - 1].focus() :
              i[0].focus();
        }

        _handleKeydown(t) {
          t.key === 'Tab' &&
              (this._lastTabNavDirection = t.shiftKey ? an : 'forward');
        }
      }
      const un = '.fixed-top, .fixed-bottom, .is-fixed, .sticky-top';
      const dn = '.sticky-top';
      const fn = 'padding-right';
      const pn = 'margin-right';
      class mn {
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
          this._setElementAttributes(this._element, fn, e => e + t),
          this._setElementAttributes(un, fn, e => e + t),
          this._setElementAttributes(dn, pn, e => e - t));
        }

        reset() {
          (this._resetElementAttributes(this._element, 'overflow'),
          this._resetElementAttributes(this._element, fn),
          this._resetElementAttributes(un, fn),
          this._resetElementAttributes(dn, pn));
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
          i && ye.setDataAttribute(t, e, i);
        }

        _resetElementAttributes(t, e) {
          this._applyManipulationCallback(t, t => {
            const i = ye.getDataAttribute(t, e);
            i !== null ?
              (ye.removeDataAttribute(t, e), t.style.setProperty(e, i)) :
              t.style.removeProperty(e);
          });
        }

        _applyManipulationCallback(t, e) {
          if (Bt(t)) {
            e(t);
          } else {
            for (const i of Ce.find(t, this._element)) {
              e(i);
            }
          }
        }
      }
      const gn = '.bs.modal';
      const _n = `hide${gn}`;
      const bn = `hidePrevented${gn}`;
      const vn = `hidden${gn}`;
      const yn = `show${gn}`;
      const wn = `shown${gn}`;
      const An = `resize${gn}`;
      const En = `click.dismiss${gn}`;
      const Cn = `mousedown.dismiss${gn}`;
      const On = `keydown.dismiss${gn}`;
      const Tn = `click${gn}.data-api`;
      const xn = 'modal-open';
      const kn = 'show';
      const Ln = 'modal-static';
      const Sn = { backdrop: !0, focus: !0, keyboard: !0 };
      const Dn = {
        backdrop: '(boolean|string)',
        focus: 'boolean',
        keyboard: 'boolean',
      };
      class $n extends Ae {
        constructor(t, e) {
          (super(t, e),
          (this._dialog = Ce.findOne('.modal-dialog', this._element)),
          (this._backdrop = this._initializeBackDrop()),
          (this._focustrap = this._initializeFocusTrap()),
          (this._isShown = !1),
          (this._isTransitioning = !1),
          (this._scrollBar = new mn()),
          this._addEventListeners());
        }

        static get Default() {
          return Sn;
        }

        static get DefaultType() {
          return Dn;
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
          ge.trigger(this._element, yn, { relatedTarget: t })
            .defaultPrevented ||
              ((this._isShown = !0),
              (this._isTransitioning = !0),
              this._scrollBar.hide(),
              document.body.classList.add(xn),
              this._adjustDialog(),
              this._backdrop.show(() => this._showElement(t)));
        }

        hide() {
          if (!this._isShown || this._isTransitioning) {
            return;
          }
          ge.trigger(this._element, _n).defaultPrevented ||
              ((this._isShown = !1),
              (this._isTransitioning = !0),
              this._focustrap.deactivate(),
              this._element.classList.remove(kn),
              this._queueCallback(
                () => this._hideModal(),
                this._element,
                this._isAnimated(),
              ));
        }

        dispose() {
          (ge.off(window, gn),
          ge.off(this._dialog, gn),
          this._backdrop.dispose(),
          this._focustrap.deactivate(),
          super.dispose());
        }

        handleUpdate() {
          this._adjustDialog();
        }

        _initializeBackDrop() {
          return new nn({
            isVisible: Boolean(this._config.backdrop),
            isAnimated: this._isAnimated(),
          });
        }

        _initializeFocusTrap() {
          return new hn({ trapElement: this._element });
        }

        _showElement(t) {
          (document.body.contains(this._element) ||
              document.body.append(this._element),
          (this._element.style.display = 'block'),
          this._element.removeAttribute('aria-hidden'),
          this._element.setAttribute('aria-modal', !0),
          this._element.setAttribute('role', 'dialog'),
          (this._element.scrollTop = 0));
          const e = Ce.findOne('.modal-body', this._dialog);
          (e && (e.scrollTop = 0),
          Qt(this._element),
          this._element.classList.add(kn));
          this._queueCallback(
            () => {
              (this._config.focus && this._focustrap.activate(),
              (this._isTransitioning = !1),
              ge.trigger(this._element, wn, { relatedTarget: t }));
            },
            this._dialog,
            this._isAnimated(),
          );
        }

        _addEventListeners() {
          (ge.on(this._element, On, t => {
            t.key === 'Escape' &&
                (this._config.keyboard ?
                  this.hide() :
                  this._triggerBackdropTransition());
          }),
          ge.on(window, An, () => {
            this._isShown && !this._isTransitioning && this._adjustDialog();
          }),
          ge.on(this._element, Cn, t => {
            ge.one(this._element, En, e => {
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
            (document.body.classList.remove(xn),
            this._resetAdjustments(),
            this._scrollBar.reset(),
            ge.trigger(this._element, vn));
          }));
        }

        _isAnimated() {
          return this._element.classList.contains('fade');
        }

        _triggerBackdropTransition() {
          if (ge.trigger(this._element, bn).defaultPrevented) {
            return;
          }
          const t =
                this._element.scrollHeight >
                document.documentElement.clientHeight;
          const e = this._element.style.overflowY;
          e === 'hidden' ||
              this._element.classList.contains(Ln) ||
              (t || (this._element.style.overflowY = 'hidden'),
              this._element.classList.add(Ln),
              this._queueCallback(() => {
                (this._element.classList.remove(Ln),
                this._queueCallback(() => {
                  this._element.style.overflowY = e;
                }, this._dialog));
              }, this._dialog),
              this._element.focus());
        }

        _adjustDialog() {
          const t =
                this._element.scrollHeight >
                document.documentElement.clientHeight;
          const e = this._scrollBar.getWidth();
          const i = e > 0;
          if (i && !t) {
            const t = Ut() ? 'paddingLeft' : 'paddingRight';
            this._element.style[t] = `${e}px`;
          }
          if (!i && t) {
            const t = Ut() ? 'paddingRight' : 'paddingLeft';
            this._element.style[t] = `${e}px`;
          }
        }

        _resetAdjustments() {
          ((this._element.style.paddingLeft = ''),
          (this._element.style.paddingRight = ''));
        }

        static jQueryInterface(t, e) {
          return this.each(function() {
            const i = $n.getOrCreateInstance(this, t);
            if (typeof t === 'string') {
              if (void 0 === i[t]) {
                throw new TypeError(`No method named "${t}"`);
              }
              i[t](e);
            }
          });
        }
      }
      (ge.on(document, Tn, '[data-bs-toggle="modal"]', function(t) {
        const e = Ce.getElementFromSelector(this);
        (['A', 'AREA'].includes(this.tagName) && t.preventDefault(),
        ge.one(e, yn, t => {
          t.defaultPrevented ||
                ge.one(e, vn, () => {
                  Rt(this) && this.focus();
                });
        }));
        const i = Ce.findOne('.modal.show');
        i && $n.getInstance(i).hide();
        $n.getOrCreateInstance(e).toggle(this);
      }),
      Oe($n),
      Gt($n));
      const In = '.bs.offcanvas';
      const Nn = '.data-api';
      const Pn = `load${In}${Nn}`;
      const jn = 'show';
      const Mn = 'showing';
      const Fn = 'hiding';
      const Hn = '.offcanvas.show';
      const Wn = `show${In}`;
      const Bn = `shown${In}`;
      const zn = `hide${In}`;
      const Rn = `hidePrevented${In}`;
      const qn = `hidden${In}`;
      const Vn = `resize${In}`;
      const Kn = `click${In}${Nn}`;
      const Qn = `keydown.dismiss${In}`;
      const Xn = { backdrop: !0, keyboard: !0, scroll: !1 };
      const Yn = {
        backdrop: '(boolean|string)',
        keyboard: 'boolean',
        scroll: 'boolean',
      };
      class Un extends Ae {
        constructor(t, e) {
          (super(t, e),
          (this._isShown = !1),
          (this._backdrop = this._initializeBackDrop()),
          (this._focustrap = this._initializeFocusTrap()),
          this._addEventListeners());
        }

        static get Default() {
          return Xn;
        }

        static get DefaultType() {
          return Yn;
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
          if (
            ge.trigger(this._element, Wn, { relatedTarget: t })
              .defaultPrevented
          ) {
            return;
          }
          ((this._isShown = !0),
          this._backdrop.show(),
          this._config.scroll || new mn().hide(),
          this._element.setAttribute('aria-modal', !0),
          this._element.setAttribute('role', 'dialog'),
          this._element.classList.add(Mn));
          this._queueCallback(
            () => {
              ((this._config.scroll && !this._config.backdrop) ||
                  this._focustrap.activate(),
              this._element.classList.add(jn),
              this._element.classList.remove(Mn),
              ge.trigger(this._element, Bn, { relatedTarget: t }));
            },
            this._element,
            !0,
          );
        }

        hide() {
          if (!this._isShown) {
            return;
          }
          if (ge.trigger(this._element, zn).defaultPrevented) {
            return;
          }
          (this._focustrap.deactivate(),
          this._element.blur(),
          (this._isShown = !1),
          this._element.classList.add(Fn),
          this._backdrop.hide());
          this._queueCallback(
            () => {
              (this._element.classList.remove(jn, Fn),
              this._element.removeAttribute('aria-modal'),
              this._element.removeAttribute('role'),
              this._config.scroll || new mn().reset(),
              ge.trigger(this._element, qn));
            },
            this._element,
            !0,
          );
        }

        dispose() {
          (this._backdrop.dispose(),
          this._focustrap.deactivate(),
          super.dispose());
        }

        _initializeBackDrop() {
          const t = Boolean(this._config.backdrop);
          return new nn({
            className: 'offcanvas-backdrop',
            isVisible: t,
            isAnimated: !0,
            rootElement: this._element.parentNode,
            clickCallback: t ?
              () => {
                this._config.backdrop !== 'static' ?
                  this.hide() :
                  ge.trigger(this._element, Rn);
              } :
              null,
          });
        }

        _initializeFocusTrap() {
          return new hn({ trapElement: this._element });
        }

        _addEventListeners() {
          ge.on(this._element, Qn, t => {
            t.key === 'Escape' &&
                (this._config.keyboard ?
                  this.hide() :
                  ge.trigger(this._element, Rn));
          });
        }

        static jQueryInterface(t) {
          return this.each(function() {
            const e = Un.getOrCreateInstance(this, t);
            if (typeof t === 'string') {
              if (void 0 === e[t] || t.startsWith('_') || t === 'constructor') {
                throw new TypeError(`No method named "${t}"`);
              }
              e[t](this);
            }
          });
        }
      }
      (ge.on(document, Kn, '[data-bs-toggle="offcanvas"]', function(t) {
        const e = Ce.getElementFromSelector(this);
        if (
          (['A', 'AREA'].includes(this.tagName) && t.preventDefault(),
          qt(this))
        ) {
          return;
        }
        ge.one(e, qn, () => {
          Rt(this) && this.focus();
        });
        const i = Ce.findOne(Hn);
        i && i !== e && Un.getInstance(i).hide();
        Un.getOrCreateInstance(e).toggle(this);
      }),
      ge.on(window, Pn, () => {
        for (const t of Ce.find(Hn)) {
          Un.getOrCreateInstance(t).show();
        }
      }),
      ge.on(window, Vn, () => {
        for (const t of Ce.find(
          '[aria-modal][class*=show][class*=offcanvas-]',
        )) {
          getComputedStyle(t).position !== 'fixed' &&
                Un.getOrCreateInstance(t).hide();
        }
      }),
      Oe(Un),
      Gt(Un));
      const Gn = {
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
      const Jn = new Set([
        'background',
        'cite',
        'href',
        'itemtype',
        'longdesc',
        'poster',
        'src',
        'xlink:href',
      ]);
      const Zn = /^(?!javascript:)(?:[a-z0-9+.-]+:|[^&:/?#]*(?:[/?#]|$))/i;
      const ts = (t, e) => {
        const i = t.nodeName.toLowerCase();
        return e.includes(i) ?
          !Jn.has(i) || Boolean(Zn.test(t.nodeValue)) :
          e.filter(t => t instanceof RegExp).some(t => t.test(i));
      };
      const es = {
        allowList: Gn,
        content: {},
        extraClass: '',
        html: !1,
        sanitize: !0,
        sanitizeFn: null,
        template: '<div></div>',
      };
      const is = {
        allowList: 'object',
        content: 'object',
        extraClass: '(string|function)',
        html: 'boolean',
        sanitize: 'boolean',
        sanitizeFn: '(null|function)',
        template: 'string',
      };
      const ns = {
        entry: '(string|element|function|null)',
        selector: '(string|element)',
      };
      class ss extends we {
        constructor(t) {
          (super(), (this._config = this._getConfig(t)));
        }

        static get Default() {
          return es;
        }

        static get DefaultType() {
          return is;
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
            super._typeCheckConfig({ selector: e, entry: i }, ns);
          }
        }

        _setContent(t, e, i) {
          const n = Ce.findOne(i, t);
          n &&
              ((e = this._resolvePossibleFunction(e)) ?
                Bt(e) ?
                  this._putElementInTemplate(zt(e), n) :
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
              const n = new window.DOMParser().parseFromString(
                t,
                'text/html',
              );
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
                  ts(e, s) || t.removeAttribute(e.nodeName);
                }
              }
              return n.body.innerHTML;
            })(t, this._config.allowList, this._config.sanitizeFn) :
            t;
        }

        _resolvePossibleFunction(t) {
          return Jt(t, [void 0, this]);
        }

        _putElementInTemplate(t, e) {
          if (this._config.html) {
            return ((e.innerHTML = ''), void e.append(t));
          }
          e.textContent = t.textContent;
        }
      }
      const os = new Set(['sanitize', 'allowList', 'sanitizeFn']);
      const rs = 'fade';
      const as = 'show';
      const ls = '.tooltip-inner';
      const cs = '.modal';
      const hs = 'hide.bs.modal';
      const us = 'hover';
      const ds = 'focus';
      const fs = 'click';
      const ps = {
        AUTO: 'auto',
        TOP: 'top',
        RIGHT: Ut() ? 'left' : 'right',
        BOTTOM: 'bottom',
        LEFT: Ut() ? 'right' : 'left',
      };
      const ms = {
        allowList: Gn,
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
      const gs = {
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
      class _s extends Ae {
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
          return ms;
        }

        static get DefaultType() {
          return gs;
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
          this._isEnabled &&
              (this._isShown() ? this._leave() : this._enter());
        }

        dispose() {
          (clearTimeout(this._timeout),
          ge.off(this._element.closest(cs), hs, this._hideModalHandler),
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
          const t = ge.trigger(
            this._element,
            this.constructor.eventName('show'),
          );
          const e = (
            Vt(this._element) || this._element.ownerDocument.documentElement
          ).contains(this._element);
          if (t.defaultPrevented || !e) {
            return;
          }
          this._disposePopper();
          const i = this._getTipElement();
          this._element.setAttribute(
            'aria-describedby',
            i.getAttribute('id'),
          );
          const { container: n } = this._config;
          if (
            (this._element.ownerDocument.documentElement.contains(this.tip) ||
                (n.append(i),
                ge.trigger(
                  this._element,
                  this.constructor.eventName('inserted'),
                )),
            (this._popper = this._createPopper(i)),
            i.classList.add(as),
            'ontouchstart' in document.documentElement)
          ) {
            for (const t of [].concat(...document.body.children)) {
              ge.on(t, 'mouseover', Kt);
            }
          }
          this._queueCallback(
            () => {
              (ge.trigger(this._element, this.constructor.eventName('shown')),
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
            ge.trigger(this._element, this.constructor.eventName('hide'))
              .defaultPrevented
          ) {
            return;
          }
          if (
            (this._getTipElement().classList.remove(as),
            'ontouchstart' in document.documentElement)
          ) {
            for (const t of [].concat(...document.body.children)) {
              ge.off(t, 'mouseover', Kt);
            }
          }
          ((this._activeTrigger[fs] = !1),
          (this._activeTrigger[ds] = !1),
          (this._activeTrigger[us] = !1),
          (this._isHovered = null));
          this._queueCallback(
            () => {
              this._isWithActiveTrigger() ||
                  (this._isHovered || this._disposePopper(),
                  this._element.removeAttribute('aria-describedby'),
                  ge.trigger(
                    this._element,
                    this.constructor.eventName('hidden'),
                  ));
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
          (e.classList.remove(rs, as),
          e.classList.add(`bs-${this.constructor.NAME}-auto`));
          const i = (t => {
            do {
              t += Math.floor(1e6 * Math.random());
            } while (document.getElementById(t));
            return t;
          })(this.constructor.NAME).toString();
          return (
            e.setAttribute('id', i),
            this._isAnimated() && e.classList.add(rs),
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
              (this._templateFactory = new ss({
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
          return { [ls]: this._getTitle() };
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
            this._config.animation ||
              (this.tip && this.tip.classList.contains(rs))
          );
        }

        _isShown() {
          return this.tip && this.tip.classList.contains(as);
        }

        _createPopper(t) {
          const e = Jt(this._config.placement, [this, t, this._element]);
          const i = ps[e.toUpperCase()];
          return It(this._element, t, this._getPopperConfig(i));
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
          return Jt(t, [this._element, this._element]);
        }

        _getPopperConfig(t) {
          const e = {
            placement: t,
            modifiers: [
              {
                name: 'flip',
                options: {
                  fallbackPlacements: this._config.fallbackPlacements,
                },
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
          return { ...e, ...Jt(this._config.popperConfig, [void 0, e]) };
        }

        _setListeners() {
          const t = this._config.trigger.split(' ');
          for (const e of t) {
            if (e === 'click') {
              ge.on(
                this._element,
                this.constructor.eventName('click'),
                this._config.selector,
                t => {
                  const e = this._initializeOnDelegatedTarget(t);
                  ((e._activeTrigger[fs] = !(
                    e._isShown() && e._activeTrigger[fs]
                  )),
                  e.toggle());
                },
              );
            } else if (e !== 'manual') {
              const t =
                    e === us ?
                      this.constructor.eventName('mouseenter') :
                      this.constructor.eventName('focusin');
              const i =
                    e === us ?
                      this.constructor.eventName('mouseleave') :
                      this.constructor.eventName('focusout');
              (ge.on(this._element, t, this._config.selector, t => {
                const e = this._initializeOnDelegatedTarget(t);
                ((e._activeTrigger[t.type === 'focusin' ? ds : us] = !0),
                e._enter());
              }),
              ge.on(this._element, i, this._config.selector, t => {
                const e = this._initializeOnDelegatedTarget(t);
                ((e._activeTrigger[t.type === 'focusout' ? ds : us] =
                      e._element.contains(t.relatedTarget)),
                e._leave());
              }));
            }
          }
          ((this._hideModalHandler = () => {
            this._element && this.hide();
          }),
          ge.on(this._element.closest(cs), hs, this._hideModalHandler));
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
          const e = ye.getDataAttributes(this._element);
          for (const t of Object.keys(e)) {
            os.has(t) && delete e[t];
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
            (t.container =
                !1 === t.container ? document.body : zt(t.container)),
            typeof t.delay === 'number' &&
                (t.delay = { show: t.delay, hide: t.delay }),
            typeof t.title === 'number' && (t.title = t.title.toString()),
            typeof t.content === 'number' &&
                (t.content = t.content.toString()),
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
            const e = _s.getOrCreateInstance(this, t);
            if (typeof t === 'string') {
              if (void 0 === e[t]) {
                throw new TypeError(`No method named "${t}"`);
              }
              e[t]();
            }
          });
        }
      }
      Gt(_s);
      const bs = '.popover-header';
      const vs = '.popover-body';
      const ys = {
        ..._s.Default,
        content: '',
        offset: [0, 8],
        placement: 'right',
        template:
              '<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
        trigger: 'click',
      };
      const ws = { ..._s.DefaultType, content: '(null|string|element|function)' };
      class As extends _s {
        static get Default() {
          return ys;
        }

        static get DefaultType() {
          return ws;
        }

        static get NAME() {
          return 'popover';
        }

        _isWithContent() {
          return this._getTitle() || this._getContent();
        }

        _getContentForTemplate() {
          return { [bs]: this._getTitle(), [vs]: this._getContent() };
        }

        _getContent() {
          return this._resolvePossibleFunction(this._config.content);
        }

        static jQueryInterface(t) {
          return this.each(function() {
            const e = As.getOrCreateInstance(this, t);
            if (typeof t === 'string') {
              if (void 0 === e[t]) {
                throw new TypeError(`No method named "${t}"`);
              }
              e[t]();
            }
          });
        }
      }
      Gt(As);
      const Es = '.bs.scrollspy';
      const Cs = `activate${Es}`;
      const Os = `click${Es}`;
      const Ts = `load${Es}.data-api`;
      const xs = 'active';
      const ks = '[href]';
      const Ls = '.nav-link';
      const Ss = `${Ls}, .nav-item > ${Ls}, .list-group-item`;
      const Ds = {
        offset: null,
        rootMargin: '0px 0px -25%',
        smoothScroll: !1,
        target: null,
        threshold: [0.1, 0.5, 1],
      };
      const $s = {
        offset: '(number|null)',
        rootMargin: 'string',
        smoothScroll: 'boolean',
        target: 'element',
        threshold: 'array',
      };
      class Is extends Ae {
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
          (this._previousScrollData = {
            visibleEntryTop: 0,
            parentScrollTop: 0,
          }),
          this.refresh());
        }

        static get Default() {
          return Ds;
        }

        static get DefaultType() {
          return $s;
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
            (t.target = zt(t.target) || document.body),
            (t.rootMargin = t.offset ?
              `${t.offset}px 0px -30%` :
              t.rootMargin),
            typeof t.threshold === 'string' &&
                (t.threshold = t.threshold
                  .split(',')
                  .map(t => Number.parseFloat(t))),
            t
          );
        }

        _maybeEnableSmoothScroll() {
          this._config.smoothScroll &&
              (ge.off(this._config.target, Os),
              ge.on(this._config.target, Os, ks, t => {
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
          return new IntersectionObserver(
            t => this._observerCallback(t),
            t,
          );
        }

        _observerCallback(t) {
          const e = t => this._targetLinks.get(`#${t.target.id}`);
          const i = t => {
            ((this._previousScrollData.visibleEntryTop =
                  t.target.offsetTop),
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
          ((this._targetLinks = new Map()),
          (this._observableSections = new Map()));
          const t = Ce.find(ks, this._config.target);
          for (const e of t) {
            if (!e.hash || qt(e)) {
              continue;
            }
            const t = Ce.findOne(decodeURI(e.hash), this._element);
            Rt(t) &&
                (this._targetLinks.set(decodeURI(e.hash), e),
                this._observableSections.set(e.hash, t));
          }
        }

        _process(t) {
          this._activeTarget !== t &&
              (this._clearActiveClass(this._config.target),
              (this._activeTarget = t),
              t.classList.add(xs),
              this._activateParents(t),
              ge.trigger(this._element, Cs, { relatedTarget: t }));
        }

        _activateParents(t) {
          if (t.classList.contains('dropdown-item')) {
            Ce.findOne(
              '.dropdown-toggle',
              t.closest('.dropdown'),
            ).classList.add(xs);
          } else {
            for (const e of Ce.parents(t, '.nav, .list-group')) {
              for (const t of Ce.prev(e, Ss)) {
                t.classList.add(xs);
              }
            }
          }
        }

        _clearActiveClass(t) {
          t.classList.remove(xs);
          const e = Ce.find(`${ks}.${xs}`, t);
          for (const t of e) {
            t.classList.remove(xs);
          }
        }

        static jQueryInterface(t) {
          return this.each(function() {
            const e = Is.getOrCreateInstance(this, t);
            if (typeof t === 'string') {
              if (void 0 === e[t] || t.startsWith('_') || t === 'constructor') {
                throw new TypeError(`No method named "${t}"`);
              }
              e[t]();
            }
          });
        }
      }
      (ge.on(window, Ts, () => {
        for (const t of Ce.find('[data-bs-spy="scroll"]')) {
          Is.getOrCreateInstance(t);
        }
      }),
      Gt(Is));
      const Ns = '.bs.tab';
      const Ps = `hide${Ns}`;
      const js = `hidden${Ns}`;
      const Ms = `show${Ns}`;
      const Fs = `shown${Ns}`;
      const Hs = `click${Ns}`;
      const Ws = `keydown${Ns}`;
      const Bs = `load${Ns}`;
      const zs = 'ArrowLeft';
      const Rs = 'ArrowRight';
      const qs = 'ArrowUp';
      const Vs = 'ArrowDown';
      const Ks = 'Home';
      const Qs = 'End';
      const Xs = 'active';
      const Ys = 'fade';
      const Us = 'show';
      const Gs = '.dropdown-toggle';
      const Js = `:not(${Gs})`;
      const Zs =
            '[data-bs-toggle="tab"], [data-bs-toggle="pill"], [data-bs-toggle="list"]';
      const to = `${`.nav-link${Js}, .list-group-item${Js}, [role="tab"]${Js}`}, ${Zs}`;
      const eo = `.${Xs}[data-bs-toggle="tab"], .${Xs}[data-bs-toggle="pill"], .${Xs}[data-bs-toggle="list"]`;
      class io extends Ae {
        constructor(t) {
          (super(t),
          (this._parent = this._element.closest(
            '.list-group, .nav, [role="tablist"]',
          )),
          this._parent &&
                (this._setInitialAttributes(this._parent, this._getChildren()),
                ge.on(this._element, Ws, t => this._keydown(t))));
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
          const i = e ? ge.trigger(e, Ps, { relatedTarget: t }) : null;
          ge.trigger(t, Ms, { relatedTarget: e }).defaultPrevented ||
              (i && i.defaultPrevented) ||
              (this._deactivate(e, t), this._activate(t, e));
        }

        _activate(t, e) {
          if (!t) {
            return;
          }
          (t.classList.add(Xs), this._activate(Ce.getElementFromSelector(t)));
          this._queueCallback(
            () => {
              t.getAttribute('role') === 'tab' ?
                (t.removeAttribute('tabindex'),
                t.setAttribute('aria-selected', !0),
                this._toggleDropDown(t, !0),
                ge.trigger(t, Fs, { relatedTarget: e })) :
                t.classList.add(Us);
            },
            t,
            t.classList.contains(Ys),
          );
        }

        _deactivate(t, e) {
          if (!t) {
            return;
          }
          (t.classList.remove(Xs),
          t.blur(),
          this._deactivate(Ce.getElementFromSelector(t)));
          this._queueCallback(
            () => {
              t.getAttribute('role') === 'tab' ?
                (t.setAttribute('aria-selected', !1),
                t.setAttribute('tabindex', '-1'),
                this._toggleDropDown(t, !1),
                ge.trigger(t, js, { relatedTarget: e })) :
                t.classList.remove(Us);
            },
            t,
            t.classList.contains(Ys),
          );
        }

        _keydown(t) {
          if (![zs, Rs, qs, Vs, Ks, Qs].includes(t.key)) {
            return;
          }
          (t.stopPropagation(), t.preventDefault());
          const e = this._getChildren().filter(t => !qt(t));
          let i;
          if ([Ks, Qs].includes(t.key)) {
            i = e[t.key === Ks ? 0 : e.length - 1];
          } else {
            const n = [Rs, Vs].includes(t.key);
            i = te(e, t.target, n, !0);
          }
          i &&
              (i.focus({ preventScroll: !0 }),
              io.getOrCreateInstance(i).show());
        }

        _getChildren() {
          return Ce.find(to, this._parent);
        }

        _getActiveElem() {
          return (
            this._getChildren().find(t => this._elemIsActive(t)) || null
          );
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
          i !== t &&
                this._setAttributeIfNotExists(i, 'role', 'presentation'),
          e || t.setAttribute('tabindex', '-1'),
          this._setAttributeIfNotExists(t, 'role', 'tab'),
          this._setInitialAttributesOnTargetPanel(t));
        }

        _setInitialAttributesOnTargetPanel(t) {
          const e = Ce.getElementFromSelector(t);
          e &&
              (this._setAttributeIfNotExists(e, 'role', 'tabpanel'),
              t.id &&
                this._setAttributeIfNotExists(e, 'aria-labelledby', `${t.id}`));
        }

        _toggleDropDown(t, e) {
          const i = this._getOuterElement(t);
          if (!i.classList.contains('dropdown')) {
            return;
          }
          const n = (t, n) => {
            const s = Ce.findOne(t, i);
            s && s.classList.toggle(n, e);
          };
          (n(Gs, Xs),
          n('.dropdown-menu', Us),
          i.setAttribute('aria-expanded', e));
        }

        _setAttributeIfNotExists(t, e, i) {
          t.hasAttribute(e) || t.setAttribute(e, i);
        }

        _elemIsActive(t) {
          return t.classList.contains(Xs);
        }

        _getInnerElement(t) {
          return t.matches(to) ? t : Ce.findOne(to, t);
        }

        _getOuterElement(t) {
          return t.closest('.nav-item, .list-group-item') || t;
        }

        static jQueryInterface(t) {
          return this.each(function() {
            const e = io.getOrCreateInstance(this);
            if (typeof t === 'string') {
              if (void 0 === e[t] || t.startsWith('_') || t === 'constructor') {
                throw new TypeError(`No method named "${t}"`);
              }
              e[t]();
            }
          });
        }
      }
      (ge.on(document, Hs, Zs, function(t) {
        (['A', 'AREA'].includes(this.tagName) && t.preventDefault(),
        qt(this) || io.getOrCreateInstance(this).show());
      }),
      ge.on(window, Bs, () => {
        for (const t of Ce.find(eo)) {
          io.getOrCreateInstance(t);
        }
      }),
      Gt(io));
      const no = '.bs.toast';
      const so = `mouseover${no}`;
      const oo = `mouseout${no}`;
      const ro = `focusin${no}`;
      const ao = `focusout${no}`;
      const lo = `hide${no}`;
      const co = `hidden${no}`;
      const ho = `show${no}`;
      const uo = `shown${no}`;
      const fo = 'hide';
      const po = 'show';
      const mo = 'showing';
      const go = { animation: 'boolean', autohide: 'boolean', delay: 'number' };
      const _o = { animation: !0, autohide: !0, delay: 5e3 };
      class bo extends Ae {
        constructor(t, e) {
          (super(t, e),
          (this._timeout = null),
          (this._hasMouseInteraction = !1),
          (this._hasKeyboardInteraction = !1),
          this._setListeners());
        }

        static get Default() {
          return _o;
        }

        static get DefaultType() {
          return go;
        }

        static get NAME() {
          return 'toast';
        }

        show() {
          if (ge.trigger(this._element, ho).defaultPrevented) {
            return;
          }
          (this._clearTimeout(),
          this._config.animation && this._element.classList.add('fade'));
          (this._element.classList.remove(fo),
          Qt(this._element),
          this._element.classList.add(po, mo),
          this._queueCallback(
            () => {
              (this._element.classList.remove(mo),
              ge.trigger(this._element, uo),
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
          if (ge.trigger(this._element, lo).defaultPrevented) {
            return;
          }
          (this._element.classList.add(mo),
          this._queueCallback(
            () => {
              (this._element.classList.add(fo),
              this._element.classList.remove(mo, po),
              ge.trigger(this._element, co));
            },
            this._element,
            this._config.animation,
          ));
        }

        dispose() {
          (this._clearTimeout(),
          this.isShown() && this._element.classList.remove(po),
          super.dispose());
        }

        isShown() {
          return this._element.classList.contains(po);
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
          (ge.on(this._element, so, t => this._onInteraction(t, !0)),
          ge.on(this._element, oo, t => this._onInteraction(t, !1)),
          ge.on(this._element, ro, t => this._onInteraction(t, !0)),
          ge.on(this._element, ao, t => this._onInteraction(t, !1)));
        }

        _clearTimeout() {
          (clearTimeout(this._timeout), (this._timeout = null));
        }

        static jQueryInterface(t) {
          return this.each(function() {
            const e = bo.getOrCreateInstance(this, t);
            if (typeof t === 'string') {
              if (void 0 === e[t]) {
                throw new TypeError(`No method named "${t}"`);
              }
              e[t](this);
            }
          });
        }
      }
      (Oe(bo),
      Gt(bo),
      document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('a[href^="#"]').forEach(t => {
          t.addEventListener('click', function(t) {
            t.preventDefault();
            const e = document.querySelector(this.getAttribute('href'));
            e && e.scrollIntoView({ behavior: 'smooth' });
          });
        });
      }));
    },
  };
  const i = {};
  function n(t) {
    const s = i[t];
    if (void 0 !== s) {
      return s.exports;
    }
    const o = (i[t] = { exports: {} });
    return (e[t](o, o.exports, n), o.exports);
  }
  ((n.m = e),
  (t = []),
  (n.O = (e, i, s, o) => {
    if (!i) {
      let r = 1 / 0;
      for (h = 0; h < t.length; h++) {
        for (var [i, s, o] = t[h], a = !0, l = 0; l < i.length; l++) {
          (!1 & o || r >= o) && Object.keys(n.O).every(t => n.O[t](i[l])) ?
            i.splice(l--, 1) :
            ((a = !1), o < r && (r = o));
        }
        if (a) {
          t.splice(h--, 1);
          const c = s();
          void 0 !== c && (e = c);
        }
      }
      return e;
    }
    o = o || 0;
    for (var h = t.length; h > 0 && t[h - 1][2] > o; h--) {
      t[h] = t[h - 1];
    }
    t[h] = [i, s, o];
  }),
  (n.d = (t, e) => {
    for (const i in e) {
      n.o(e, i) &&
          !n.o(t, i) &&
          Object.defineProperty(t, i, { enumerable: !0, get: e[i] });
    }
  }),
  (n.o = (t, e) => Object.prototype.hasOwnProperty.call(t, e)),
  (n.r = t => {
    (typeof Symbol !== 'undefined' &&
        Symbol.toStringTag &&
        Object.defineProperty(t, Symbol.toStringTag, { value: 'Module' }),
    Object.defineProperty(t, '__esModule', { value: !0 }));
  }),
  (() => {
    const t = { 381: 0, 912: 0, 516: 0 };
    n.O.j = e => t[e] === 0;
    const e = (e, i) => {
      let s;
      let o;
      const [r, a, l] = i;
      let c = 0;
      if (r.some(e => t[e] !== 0)) {
        for (s in a) {
          n.o(a, s) && (n.m[s] = a[s]);
        }
        if (l) {
          var h = l(n);
        }
      }
      for (e && e(i); c < r.length; c++) {
        ((o = r[c]), n.o(t, o) && t[o] && t[o][0](), (t[o] = 0));
      }
      return n.O(h);
    };
    const i = (self.webpackChunk = self.webpackChunk || []);
    (i.forEach(e.bind(null, 0)), (i.push = e.bind(null, i.push.bind(i))));
  })(),
  n.O(void 0, [912, 516], () => n(929)),
  n.O(void 0, [912, 516], () => n(442)));
  let s = n.O(void 0, [912, 516], () => n(796));
  s = n.O(s);
})();
