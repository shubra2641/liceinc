/*!
 * Chart.js v4.5.0
 * https://www.chartjs.org
 * (c) 2025 Chart.js Contributors
 * Released under the MIT License
 */
!(function(t, e) {
  typeof exports === 'object' && typeof module !== 'undefined' ?
    (module.exports = e()) :
    typeof define === 'function' && define.amd ?
      define(e) :
      ((t = typeof globalThis !== 'undefined' ? globalThis : t || self).Chart =
          e());
})(this, () => {
  'use strict';
  let t = Object.freeze({
    __proto__: null,
    get Colors() {
      return Jo;
    },
    get Decimation() {
      return ta;
    },
    get Filler() {
      return ba;
    },
    get Legend() {
      return Ma;
    },
    get SubTitle() {
      return Pa;
    },
    get Title() {
      return ka;
    },
    get Tooltip() {
      return Na;
    },
  });
  function e() {}
  const i = (() => {
    let t = 0;
    return () => t++;
  })();
  function s(t) {
    return t == null;
  }
  function n(t) {
    if (Array.isArray && Array.isArray(t)) {
      return !0;
    }
    const e = Object.prototype.toString.call(t);
    return e.slice(0, 7) === '[object' && e.slice(-6) === 'Array]';
  }
  function o(t) {
    return (
      t !== null && Object.prototype.toString.call(t) === '[object Object]'
    );
  }
  function a(t) {
    return (typeof t === 'number' || t instanceof Number) && isFinite(+t);
  }
  function r(t, e) {
    return a(t) ? t : e;
  }
  function l(t, e) {
    return void 0 === t ? e : t;
  }
  const h = (t, e) =>
    typeof t === 'string' && t.endsWith('%') ? parseFloat(t) / 100 : +t / e;
  const c = (t, e) =>
    typeof t === 'string' && t.endsWith('%') ? (parseFloat(t) / 100) * e : +t;
  function d(t, e, i) {
    if (t && typeof t.call === 'function') {
      return t.apply(i, e);
    }
  }
  function u(t, e, i, s) {
    let a, r, l;
    if (n(t)) {
      if (((r = t.length), s)) {
        for (a = r - 1; a >= 0; a--) {
          e.call(i, t[a], a);
        }
      } else {
        for (a = 0; a < r; a++) {
          e.call(i, t[a], a);
        }
      }
    } else if (o(t)) {
      for (l = Object.keys(t), r = l.length, a = 0; a < r; a++) {
        e.call(i, t[l[a]], l[a]);
      }
    }
  }
  function f(t, e) {
    let i, s, n, o;
    if (!t || !e || t.length !== e.length) {
      return !1;
    }
    for (i = 0, s = t.length; i < s; ++i) {
      if (
        ((n = t[i]),
        (o = e[i]),
        n.datasetIndex !== o.datasetIndex || n.index !== o.index)
      ) {
        return !1;
      }
    }
    return !0;
  }
  function g(t) {
    if (n(t)) {
      return t.map(g);
    }
    if (o(t)) {
      const e = Object.create(null);
      const i = Object.keys(t);
      const s = i.length;
      let n = 0;
      for (; n < s; ++n) {
        e[i[n]] = g(t[i[n]]);
      }
      return e;
    }
    return t;
  }
  function p(t) {
    return ['__proto__', 'prototype', 'constructor'].indexOf(t) === -1;
  }
  function m(t, e, i, s) {
    if (!p(t)) {
      return;
    }
    const n = e[t];
    const a = i[t];
    o(n) && o(a) ? x(n, a, s) : (e[t] = g(a));
  }
  function x(t, e, i) {
    const s = n(e) ? e : [e];
    const a = s.length;
    if (!o(t)) {
      return t;
    }
    const r = (i = i || {}).merger || m;
    let l;
    for (let e = 0; e < a; ++e) {
      if (((l = s[e]), !o(l))) {
        continue;
      }
      const n = Object.keys(l);
      for (let e = 0, s = n.length; e < s; ++e) {
        r(n[e], t, l, i);
      }
    }
    return t;
  }
  function b(t, e) {
    return x(t, e, { merger: _ });
  }
  function _(t, e, i) {
    if (!p(t)) {
      return;
    }
    const s = e[t];
    const n = i[t];
    o(s) && o(n) ?
      b(s, n) :
      Object.prototype.hasOwnProperty.call(e, t) || (e[t] = g(n));
  }
  const y = { '': t => t, x: t => t.x, y: t => t.y };
  function v(t) {
    const e = t.split('.');
    const i = [];
    let s = '';
    for (const t of e) {
      ((s += t),
      s.endsWith('\\') ? (s = `${s.slice(0, -1)}.`) : (i.push(s), (s = '')));
    }
    return i;
  }
  function M(t, e) {
    const i =
      y[e] ||
      (y[e] = (function(t) {
        const e = v(t);
        return t => {
          for (const i of e) {
            if (i === '') {
              break;
            }
            t = t && t[i];
          }
          return t;
        };
      })(e));
    return i(t);
  }
  function w(t) {
    return t.charAt(0).toUpperCase() + t.slice(1);
  }
  const k = t => void 0 !== t;
  const S = t => typeof t === 'function';
  const P = (t, e) => {
    if (t.size !== e.size) {
      return !1;
    }
    for (const i of t) {
      if (!e.has(i)) {
        return !1;
      }
    }
    return !0;
  };
  function D(t) {
    return (
      t.type === 'mouseup' || t.type === 'click' || t.type === 'contextmenu'
    );
  }
  const C = Math.PI;
  const O = 2 * C;
  const A = O + C;
  const T = Number.POSITIVE_INFINITY;
  const L = C / 180;
  const E = C / 2;
  const R = C / 4;
  const I = (2 * C) / 3;
  const z = Math.log10;
  const F = Math.sign;
  function V(t, e, i) {
    return Math.abs(t - e) < i;
  }
  function B(t) {
    const e = Math.round(t);
    t = V(t, e, t / 1e3) ? e : t;
    const i = Math.pow(10, Math.floor(z(t)));
    const s = t / i;
    return (s <= 1 ? 1 : s <= 2 ? 2 : s <= 5 ? 5 : 10) * i;
  }
  function W(t) {
    const e = [];
    const i = Math.sqrt(t);
    let s;
    for (s = 1; s < i; s++) {
      t % s == 0 && (e.push(s), e.push(t / s));
    }
    return (i === (0 | i) && e.push(i), e.sort((t, e) => t - e).pop(), e);
  }
  function N(t) {
    return (
      !(function(t) {
        return (
          typeof t === 'symbol' ||
          (typeof t === 'object' &&
            t !== null &&
            !(Symbol.toPrimitive in t || 'toString' in t || 'valueOf' in t))
        );
      })(t) &&
      !isNaN(parseFloat(t)) &&
      isFinite(t)
    );
  }
  function H(t, e) {
    const i = Math.round(t);
    return i - e <= t && i + e >= t;
  }
  function j(t, e, i) {
    let s, n, o;
    for (s = 0, n = t.length; s < n; s++) {
      ((o = t[s][i]),
      isNaN(o) ||
          ((e.min = Math.min(e.min, o)), (e.max = Math.max(e.max, o))));
    }
  }
  function $(t) {
    return t * (C / 180);
  }
  function Y(t) {
    return t * (180 / C);
  }
  function U(t) {
    if (!a(t)) {
      return;
    }
    let e = 1;
    let i = 0;
    for (; Math.round(t * e) / e !== t;) {
      ((e *= 10), i++);
    }
    return i;
  }
  function X(t, e) {
    const i = e.x - t.x;
    const s = e.y - t.y;
    const n = Math.sqrt(i * i + s * s);
    let o = Math.atan2(s, i);
    return (o < -0.5 * C && (o += O), { angle: o, distance: n });
  }
  function q(t, e) {
    return Math.sqrt(Math.pow(e.x - t.x, 2) + Math.pow(e.y - t.y, 2));
  }
  function K(t, e) {
    return ((t - e + A) % O) - C;
  }
  function G(t) {
    return ((t % O) + O) % O;
  }
  function J(t, e, i, s) {
    const n = G(t);
    const o = G(e);
    const a = G(i);
    const r = G(o - n);
    const l = G(a - n);
    const h = G(n - o);
    const c = G(n - a);
    return n === o || n === a || (s && o === a) || (r > l && h < c);
  }
  function Z(t, e, i) {
    return Math.max(e, Math.min(i, t));
  }
  function Q(t) {
    return Z(t, -32768, 32767);
  }
  function tt(t, e, i, s = 1e-6) {
    return t >= Math.min(e, i) - s && t <= Math.max(e, i) + s;
  }
  function et(t, e, i) {
    i = i || (i => t[i] < e);
    let s;
    let n = t.length - 1;
    let o = 0;
    for (; n - o > 1;) {
      ((s = (o + n) >> 1), i(s) ? (o = s) : (n = s));
    }
    return { lo: o, hi: n };
  }
  const it = (t, e, i, s) =>
    et(
      t,
      i,
      s ?
        s => {
          const n = t[s][e];
          return n < i || (n === i && t[s + 1][e] === i);
        } :
        s => t[s][e] < i,
    );
  const st = (t, e, i) => et(t, i, s => t[s][e] >= i);
  function nt(t, e, i) {
    let s = 0;
    let n = t.length;
    for (; s < n && t[s] < e;) {
      s++;
    }
    for (; n > s && t[n - 1] > i;) {
      n--;
    }
    return s > 0 || n < t.length ? t.slice(s, n) : t;
  }
  const ot = ['push', 'pop', 'shift', 'splice', 'unshift'];
  function at(t, e) {
    t._chartjs ?
      t._chartjs.listeners.push(e) :
      (Object.defineProperty(t, '_chartjs', {
        configurable: !0,
        enumerable: !1,
        value: { listeners: [e] },
      }),
      ot.forEach(e => {
        const i = `_onData${w(e)}`;
        const s = t[e];
        Object.defineProperty(t, e, {
          configurable: !0,
          enumerable: !1,
          value(...e) {
            const n = s.apply(this, e);
            return (
              t._chartjs.listeners.forEach(t => {
                typeof t[i] === 'function' && t[i](...e);
              }),
              n
            );
          },
        });
      }));
  }
  function rt(t, e) {
    const i = t._chartjs;
    if (!i) {
      return;
    }
    const s = i.listeners;
    const n = s.indexOf(e);
    (n !== -1 && s.splice(n, 1),
    s.length > 0 ||
        (ot.forEach(e => {
          delete t[e];
        }),
        delete t._chartjs));
  }
  function lt(t) {
    const e = new Set(t);
    return e.size === t.length ? t : Array.from(e);
  }
  const ht =
    typeof window === 'undefined' ?
      function(t) {
        return t();
      } :
      window.requestAnimationFrame;
  function ct(t, e) {
    let i = [];
    let s = !1;
    return function(...n) {
      ((i = n),
      s ||
          ((s = !0),
          ht.call(window, () => {
            ((s = !1), t.apply(e, i));
          })));
    };
  }
  function dt(t, e) {
    let i;
    return function(...s) {
      return (
        e ? (clearTimeout(i), (i = setTimeout(t, e, s))) : t.apply(this, s),
        e
      );
    };
  }
  const ut = t => (t === 'start' ? 'left' : t === 'end' ? 'right' : 'center');
  const ft = (t, e, i) => (t === 'start' ? e : t === 'end' ? i : (e + i) / 2);
  const gt = (t, e, i, s) =>
    t === (s ? 'left' : 'right') ? i : t === 'center' ? (e + i) / 2 : e;
  function pt(t, e, i) {
    const n = e.length;
    let o = 0;
    let a = n;
    if (t._sorted) {
      const { iScale: r, vScale: l, _parsed: h } = t;
      const c = t.dataset && t.dataset.options ? t.dataset.options.spanGaps : null;
      const d = r.axis;
      const { min: u, max: f, minDefined: g, maxDefined: p } = r.getUserBounds();
      if (g) {
        if (
          ((o = Math.min(
            it(h, d, u).lo,
            i ? n : it(e, d, r.getPixelForValue(u)).lo,
          )),
          c)
        ) {
          const t = h
            .slice(0, o + 1)
            .reverse()
            .findIndex(t => !s(t[l.axis]));
          o -= Math.max(0, t);
        }
        o = Z(o, 0, n - 1);
      }
      if (p) {
        let t = Math.max(
          it(h, r.axis, f, !0).hi + 1,
          i ? 0 : it(e, d, r.getPixelForValue(f), !0).hi + 1,
        );
        if (c) {
          const e = h.slice(t - 1).findIndex(t => !s(t[l.axis]));
          t += Math.max(0, e);
        }
        a = Z(t, o, n) - o;
      } else {
        a = n - o;
      }
    }
    return { start: o, count: a };
  }
  function mt(t) {
    const { xScale: e, yScale: i, _scaleRanges: s } = t;
    const n = { xmin: e.min, xmax: e.max, ymin: i.min, ymax: i.max };
    if (!s) {
      return ((t._scaleRanges = n), !0);
    }
    const o =
      s.xmin !== e.min ||
      s.xmax !== e.max ||
      s.ymin !== i.min ||
      s.ymax !== i.max;
    return (Object.assign(s, n), o);
  }
  class xt {
    constructor() {
      ((this._request = null),
      (this._charts = new Map()),
      (this._running = !1),
      (this._lastDate = void 0));
    }

    _notify(t, e, i, s) {
      const n = e.listeners[s];
      const o = e.duration;
      n.forEach(s =>
        s({
          chart: t,
          initial: e.initial,
          numSteps: o,
          currentStep: Math.min(i - e.start, o),
        }),
      );
    }

    _refresh() {
      this._request ||
        ((this._running = !0),
        (this._request = ht.call(window, () => {
          (this._update(),
          (this._request = null),
          this._running && this._refresh());
        })));
    }

    _update(t = Date.now()) {
      let e = 0;
      (this._charts.forEach((i, s) => {
        if (!i.running || !i.items.length) {
          return;
        }
        const n = i.items;
        let o;
        let a = n.length - 1;
        let r = !1;
        for (; a >= 0; --a) {
          ((o = n[a]),
          o._active ?
            (o._total > i.duration && (i.duration = o._total),
            o.tick(t),
            (r = !0)) :
            ((n[a] = n[n.length - 1]), n.pop()));
        }
        (r && (s.draw(), this._notify(s, i, t, 'progress')),
        n.length ||
            ((i.running = !1),
            this._notify(s, i, t, 'complete'),
            (i.initial = !1)),
        (e += n.length));
      }),
      (this._lastDate = t),
      e === 0 && (this._running = !1));
    }

    _getAnims(t) {
      const e = this._charts;
      let i = e.get(t);
      return (
        i ||
          ((i = {
            running: !1,
            initial: !0,
            items: [],
            listeners: { complete: [], progress: [] },
          }),
          e.set(t, i)),
        i
      );
    }

    listen(t, e, i) {
      this._getAnims(t).listeners[e].push(i);
    }

    add(t, e) {
      e && e.length && this._getAnims(t).items.push(...e);
    }

    has(t) {
      return this._getAnims(t).items.length > 0;
    }

    start(t) {
      const e = this._charts.get(t);
      e &&
        ((e.running = !0),
        (e.start = Date.now()),
        (e.duration = e.items.reduce((t, e) => Math.max(t, e._duration), 0)),
        this._refresh());
    }

    running(t) {
      if (!this._running) {
        return !1;
      }
      const e = this._charts.get(t);
      return !!(e && e.running && e.items.length);
    }

    stop(t) {
      const e = this._charts.get(t);
      if (!e || !e.items.length) {
        return;
      }
      const i = e.items;
      let s = i.length - 1;
      for (; s >= 0; --s) {
        i[s].cancel();
      }
      ((e.items = []), this._notify(t, e, Date.now(), 'complete'));
    }

    remove(t) {
      return this._charts.delete(t);
    }
  }
  const bt = new xt();
  /*!
   * @kurkle/color v0.3.2
   * https://github.com/kurkle/color#readme
   * (c) 2023 Jukka Kurkela
   * Released under the MIT License
   */ function _t(t) {
    return (t + 0.5) | 0;
  }
  const yt = (t, e, i) => Math.max(Math.min(t, i), e);
  function vt(t) {
    return yt(_t(2.55 * t), 0, 255);
  }
  function Mt(t) {
    return yt(_t(255 * t), 0, 255);
  }
  function wt(t) {
    return yt(_t(t / 2.55) / 100, 0, 1);
  }
  function kt(t) {
    return yt(_t(100 * t), 0, 100);
  }
  const St = {
    0: 0,
    1: 1,
    2: 2,
    3: 3,
    4: 4,
    5: 5,
    6: 6,
    7: 7,
    8: 8,
    9: 9,
    A: 10,
    B: 11,
    C: 12,
    D: 13,
    E: 14,
    F: 15,
    a: 10,
    b: 11,
    c: 12,
    d: 13,
    e: 14,
    f: 15,
  };
  const Pt = [...'0123456789ABCDEF'];
  const Dt = t => Pt[15 & t];
  const Ct = t => Pt[(240 & t) >> 4] + Pt[15 & t];
  const Ot = t => (240 & t) >> 4 == (15 & t);
  function At(t) {
    const e = (t => Ot(t.r) && Ot(t.g) && Ot(t.b) && Ot(t.a))(t) ? Dt : Ct;
    return t ?
      `#${
        e(t.r)
      }${e(t.g)
      }${e(t.b)
      }${((t, e) => (t < 255 ? e(t) : ''))(t.a, e)}` :
      void 0;
  }
  const Tt =
    /^(hsla?|hwb|hsv)\(\s*([-+.e\d]+)(?:deg)?[\s,]+([-+.e\d]+)%[\s,]+([-+.e\d]+)%(?:[\s,]+([-+.e\d]+)(%)?)?\s*\)$/;
  function Lt(t, e, i) {
    const s = e * Math.min(i, 1 - i);
    const n = (e, n = (e + t / 30) % 12) =>
      i - s * Math.max(Math.min(n - 3, 9 - n, 1), -1);
    return [n(0), n(8), n(4)];
  }
  function Et(t, e, i) {
    const s = (s, n = (s + t / 60) % 6) =>
      i - i * e * Math.max(Math.min(n, 4 - n, 1), 0);
    return [s(5), s(3), s(1)];
  }
  function Rt(t, e, i) {
    const s = Lt(t, 1, 0.5);
    let n;
    for (
      e + i > 1 && ((n = 1 / (e + i)), (e *= n), (i *= n)), n = 0;
      n < 3;
      n++
    ) {
      ((s[n] *= 1 - e - i), (s[n] += e));
    }
    return s;
  }
  function It(t) {
    const e = t.r / 255;
    const i = t.g / 255;
    const s = t.b / 255;
    const n = Math.max(e, i, s);
    const o = Math.min(e, i, s);
    const a = (n + o) / 2;
    let r, l, h;
    return (
      n !== o &&
        ((h = n - o),
        (l = a > 0.5 ? h / (2 - n - o) : h / (n + o)),
        (r = (function(t, e, i, s, n) {
          return t === n ?
            (e - i) / s + (e < i ? 6 : 0) :
            e === n ?
              (i - t) / s + 2 :
              (t - e) / s + 4;
        })(e, i, s, h, n)),
        (r = 60 * r + 0.5)),
      [0 | r, l || 0, a]
    );
  }
  function zt(t, e, i, s) {
    return (Array.isArray(e) ? t(e[0], e[1], e[2]) : t(e, i, s)).map(Mt);
  }
  function Ft(t, e, i) {
    return zt(Lt, t, e, i);
  }
  function Vt(t) {
    return ((t % 360) + 360) % 360;
  }
  function Bt(t) {
    const e = Tt.exec(t);
    let i;
    let s = 255;
    if (!e) {
      return;
    }
    e[5] !== i && (s = e[6] ? vt(+e[5]) : Mt(+e[5]));
    const n = Vt(+e[2]);
    const o = +e[3] / 100;
    const a = +e[4] / 100;
    return (
      (i =
        e[1] === 'hwb' ?
          (function(t, e, i) {
            return zt(Rt, t, e, i);
          })(n, o, a) :
          e[1] === 'hsv' ?
            (function(t, e, i) {
              return zt(Et, t, e, i);
            })(n, o, a) :
            Ft(n, o, a)),
      { r: i[0], g: i[1], b: i[2], a: s }
    );
  }
  const Wt = {
    x: 'dark',
    Z: 'light',
    Y: 're',
    X: 'blu',
    W: 'gr',
    V: 'medium',
    U: 'slate',
    A: 'ee',
    T: 'ol',
    S: 'or',
    B: 'ra',
    C: 'lateg',
    D: 'ights',
    R: 'in',
    Q: 'turquois',
    E: 'hi',
    P: 'ro',
    O: 'al',
    N: 'le',
    M: 'de',
    L: 'yello',
    F: 'en',
    K: 'ch',
    G: 'arks',
    H: 'ea',
    I: 'ightg',
    J: 'wh',
  };
  const Nt = {
    OiceXe: 'f0f8ff',
    antiquewEte: 'faebd7',
    aqua: 'ffff',
    aquamarRe: '7fffd4',
    azuY: 'f0ffff',
    beige: 'f5f5dc',
    bisque: 'ffe4c4',
    black: '0',
    blanKedOmond: 'ffebcd',
    Xe: 'ff',
    XeviTet: '8a2be2',
    bPwn: 'a52a2a',
    burlywood: 'deb887',
    caMtXe: '5f9ea0',
    KartYuse: '7fff00',
    KocTate: 'd2691e',
    cSO: 'ff7f50',
    cSnflowerXe: '6495ed',
    cSnsilk: 'fff8dc',
    crimson: 'dc143c',
    cyan: 'ffff',
    xXe: '8b',
    xcyan: '8b8b',
    xgTMnPd: 'b8860b',
    xWay: 'a9a9a9',
    xgYF: '6400',
    xgYy: 'a9a9a9',
    xkhaki: 'bdb76b',
    xmagFta: '8b008b',
    xTivegYF: '556b2f',
    xSange: 'ff8c00',
    xScEd: '9932cc',
    xYd: '8b0000',
    xsOmon: 'e9967a',
    xsHgYF: '8fbc8f',
    xUXe: '483d8b',
    xUWay: '2f4f4f',
    xUgYy: '2f4f4f',
    xQe: 'ced1',
    xviTet: '9400d3',
    dAppRk: 'ff1493',
    dApskyXe: 'bfff',
    dimWay: '696969',
    dimgYy: '696969',
    dodgerXe: '1e90ff',
    fiYbrick: 'b22222',
    flSOwEte: 'fffaf0',
    foYstWAn: '228b22',
    fuKsia: 'ff00ff',
    gaRsbSo: 'dcdcdc',
    ghostwEte: 'f8f8ff',
    gTd: 'ffd700',
    gTMnPd: 'daa520',
    Way: '808080',
    gYF: '8000',
    gYFLw: 'adff2f',
    gYy: '808080',
    honeyMw: 'f0fff0',
    hotpRk: 'ff69b4',
    RdianYd: 'cd5c5c',
    Rdigo: '4b0082',
    ivSy: 'fffff0',
    khaki: 'f0e68c',
    lavFMr: 'e6e6fa',
    lavFMrXsh: 'fff0f5',
    lawngYF: '7cfc00',
    NmoncEffon: 'fffacd',
    ZXe: 'add8e6',
    ZcSO: 'f08080',
    Zcyan: 'e0ffff',
    ZgTMnPdLw: 'fafad2',
    ZWay: 'd3d3d3',
    ZgYF: '90ee90',
    ZgYy: 'd3d3d3',
    ZpRk: 'ffb6c1',
    ZsOmon: 'ffa07a',
    ZsHgYF: '20b2aa',
    ZskyXe: '87cefa',
    ZUWay: '778899',
    ZUgYy: '778899',
    ZstAlXe: 'b0c4de',
    ZLw: 'ffffe0',
    lime: 'ff00',
    limegYF: '32cd32',
    lRF: 'faf0e6',
    magFta: 'ff00ff',
    maPon: '800000',
    VaquamarRe: '66cdaa',
    VXe: 'cd',
    VScEd: 'ba55d3',
    VpurpN: '9370db',
    VsHgYF: '3cb371',
    VUXe: '7b68ee',
    VsprRggYF: 'fa9a',
    VQe: '48d1cc',
    VviTetYd: 'c71585',
    midnightXe: '191970',
    mRtcYam: 'f5fffa',
    mistyPse: 'ffe4e1',
    moccasR: 'ffe4b5',
    navajowEte: 'ffdead',
    navy: '80',
    Tdlace: 'fdf5e6',
    Tive: '808000',
    TivedBb: '6b8e23',
    Sange: 'ffa500',
    SangeYd: 'ff4500',
    ScEd: 'da70d6',
    pOegTMnPd: 'eee8aa',
    pOegYF: '98fb98',
    pOeQe: 'afeeee',
    pOeviTetYd: 'db7093',
    papayawEp: 'ffefd5',
    pHKpuff: 'ffdab9',
    peru: 'cd853f',
    pRk: 'ffc0cb',
    plum: 'dda0dd',
    powMrXe: 'b0e0e6',
    purpN: '800080',
    YbeccapurpN: '663399',
    Yd: 'ff0000',
    Psybrown: 'bc8f8f',
    PyOXe: '4169e1',
    saddNbPwn: '8b4513',
    sOmon: 'fa8072',
    sandybPwn: 'f4a460',
    sHgYF: '2e8b57',
    sHshell: 'fff5ee',
    siFna: 'a0522d',
    silver: 'c0c0c0',
    skyXe: '87ceeb',
    UXe: '6a5acd',
    UWay: '708090',
    UgYy: '708090',
    snow: 'fffafa',
    sprRggYF: 'ff7f',
    stAlXe: '4682b4',
    tan: 'd2b48c',
    teO: '8080',
    tEstN: 'd8bfd8',
    tomato: 'ff6347',
    Qe: '40e0d0',
    viTet: 'ee82ee',
    JHt: 'f5deb3',
    wEte: 'ffffff',
    wEtesmoke: 'f5f5f5',
    Lw: 'ffff00',
    LwgYF: '9acd32',
  };
  let Ht;
  function jt(t) {
    Ht ||
      ((Ht = (function() {
        const t = {};
        const e = Object.keys(Nt);
        const i = Object.keys(Wt);
        let s, n, o, a, r;
        for (s = 0; s < e.length; s++) {
          for (a = r = e[s], n = 0; n < i.length; n++) {
            ((o = i[n]), (r = r.replace(o, Wt[o])));
          }
          ((o = parseInt(Nt[a], 16)),
          (t[r] = [(o >> 16) & 255, (o >> 8) & 255, 255 & o]));
        }
        return t;
      })()),
      (Ht.transparent = [0, 0, 0, 0]));
    const e = Ht[t.toLowerCase()];
    return e && { r: e[0], g: e[1], b: e[2], a: e.length === 4 ? e[3] : 255 };
  }
  const $t =
    /^rgba?\(\s*([-+.\d]+)(%)?[\s,]+([-+.e\d]+)(%)?[\s,]+([-+.e\d]+)(%)?(?:[\s,/]+([-+.e\d]+)(%)?)?\s*\)$/;
  const Yt = t =>
    t <= 0.0031308 ? 12.92 * t : 1.055 * Math.pow(t, 1 / 2.4) - 0.055;
  const Ut = t => (t <= 0.04045 ? t / 12.92 : Math.pow((t + 0.055) / 1.055, 2.4));
  function Xt(t, e, i) {
    if (t) {
      let s = It(t);
      ((s[e] = Math.max(0, Math.min(s[e] + s[e] * i, e === 0 ? 360 : 1))),
      (s = Ft(s)),
      (t.r = s[0]),
      (t.g = s[1]),
      (t.b = s[2]));
    }
  }
  function qt(t, e) {
    return t ? Object.assign(e || {}, t) : t;
  }
  function Kt(t) {
    let e = { r: 0, g: 0, b: 0, a: 255 };
    return (
      Array.isArray(t) ?
        t.length >= 3 &&
          ((e = { r: t[0], g: t[1], b: t[2], a: 255 }),
          t.length > 3 && (e.a = Mt(t[3]))) :
        ((e = qt(t, { r: 0, g: 0, b: 0, a: 1 })).a = Mt(e.a)),
      e
    );
  }
  function Gt(t) {
    return t.charAt(0) === 'r' ?
      (function(t) {
        const e = $t.exec(t);
        let i;
        let s;
        let n;
        let o = 255;
        if (e) {
          if (e[7] !== i) {
            const t = +e[7];
            o = e[8] ? vt(t) : yt(255 * t, 0, 255);
          }
          return (
            (i = +e[1]),
            (s = +e[3]),
            (n = +e[5]),
            (i = 255 & (e[2] ? vt(i) : yt(i, 0, 255))),
            (s = 255 & (e[4] ? vt(s) : yt(s, 0, 255))),
            (n = 255 & (e[6] ? vt(n) : yt(n, 0, 255))),
            { r: i, g: s, b: n, a: o }
          );
        }
      })(t) :
      Bt(t);
  }
  class Jt {
    constructor(t) {
      if (t instanceof Jt) {
        return t;
      }
      const e = typeof t;
      let i;
      let s, n, o;
      (e === 'object' ?
        (i = Kt(t)) :
        e === 'string' &&
          ((o = (s = t).length),
          s[0] === '#' &&
            (o === 4 || o === 5 ?
              (n = {
                r: 255 & (17 * St[s[1]]),
                g: 255 & (17 * St[s[2]]),
                b: 255 & (17 * St[s[3]]),
                a: o === 5 ? 17 * St[s[4]] : 255,
              }) :
              (o !== 7 && o !== 9) ||
                (n = {
                  r: (St[s[1]] << 4) | St[s[2]],
                  g: (St[s[3]] << 4) | St[s[4]],
                  b: (St[s[5]] << 4) | St[s[6]],
                  a: o === 9 ? (St[s[7]] << 4) | St[s[8]] : 255,
                })),
          (i = n || jt(t) || Gt(t))),
      (this._rgb = i),
      (this._valid = !!i));
    }

    get valid() {
      return this._valid;
    }

    get rgb() {
      const t = qt(this._rgb);
      return (t && (t.a = wt(t.a)), t);
    }

    set rgb(t) {
      this._rgb = Kt(t);
    }

    rgbString() {
      return this._valid ?
        (t = this._rgb) &&
            (t.a < 255 ?
              `rgba(${t.r}, ${t.g}, ${t.b}, ${wt(t.a)})` :
              `rgb(${t.r}, ${t.g}, ${t.b})`) :
        void 0;
    }

    hexString() {
      return this._valid ? At(this._rgb) : void 0;
    }

    hslString() {
      return this._valid ?
        (function(t) {
          if (!t) {
            return;
          }
          const e = It(t);
          const i = e[0];
          const s = kt(e[1]);
          const n = kt(e[2]);
          return t.a < 255 ?
            `hsla(${i}, ${s}%, ${n}%, ${wt(t.a)})` :
            `hsl(${i}, ${s}%, ${n}%)`;
        })(this._rgb) :
        void 0;
    }

    mix(t, e) {
      if (t) {
        const i = this.rgb;
        const s = t.rgb;
        let n;
        const o = e === n ? 0.5 : e;
        const a = 2 * o - 1;
        const r = i.a - s.a;
        const l = ((a * r == -1 ? a : (a + r) / (1 + a * r)) + 1) / 2;
        ((n = 1 - l),
        (i.r = 255 & (l * i.r + n * s.r + 0.5)),
        (i.g = 255 & (l * i.g + n * s.g + 0.5)),
        (i.b = 255 & (l * i.b + n * s.b + 0.5)),
        (i.a = o * i.a + (1 - o) * s.a),
        (this.rgb = i));
      }
      return this;
    }

    interpolate(t, e) {
      return (
        t &&
          (this._rgb = (function(t, e, i) {
            const s = Ut(wt(t.r));
            const n = Ut(wt(t.g));
            const o = Ut(wt(t.b));
            return {
              r: Mt(Yt(s + i * (Ut(wt(e.r)) - s))),
              g: Mt(Yt(n + i * (Ut(wt(e.g)) - n))),
              b: Mt(Yt(o + i * (Ut(wt(e.b)) - o))),
              a: t.a + i * (e.a - t.a),
            };
          })(this._rgb, t._rgb, e)),
        this
      );
    }

    clone() {
      return new Jt(this.rgb);
    }

    alpha(t) {
      return ((this._rgb.a = Mt(t)), this);
    }

    clearer(t) {
      return ((this._rgb.a *= 1 - t), this);
    }

    greyscale() {
      const t = this._rgb;
      const e = _t(0.3 * t.r + 0.59 * t.g + 0.11 * t.b);
      return ((t.r = t.g = t.b = e), this);
    }

    opaquer(t) {
      return ((this._rgb.a *= 1 + t), this);
    }

    negate() {
      const t = this._rgb;
      return ((t.r = 255 - t.r), (t.g = 255 - t.g), (t.b = 255 - t.b), this);
    }

    lighten(t) {
      return (Xt(this._rgb, 2, t), this);
    }

    darken(t) {
      return (Xt(this._rgb, 2, -t), this);
    }

    saturate(t) {
      return (Xt(this._rgb, 1, t), this);
    }

    desaturate(t) {
      return (Xt(this._rgb, 1, -t), this);
    }

    rotate(t) {
      return (
        (function(t, e) {
          let i = It(t);
          ((i[0] = Vt(i[0] + e)),
          (i = Ft(i)),
          (t.r = i[0]),
          (t.g = i[1]),
          (t.b = i[2]));
        })(this._rgb, t),
        this
      );
    }
  }
  function Zt(t) {
    if (t && typeof t === 'object') {
      const e = t.toString();
      return e === '[object CanvasPattern]' || e === '[object CanvasGradient]';
    }
    return !1;
  }
  function Qt(t) {
    return Zt(t) ? t : new Jt(t);
  }
  function te(t) {
    return Zt(t) ? t : new Jt(t).saturate(0.5).darken(0.1).hexString();
  }
  const ee = ['x', 'y', 'borderWidth', 'radius', 'tension'];
  const ie = ['color', 'borderColor', 'backgroundColor'];
  const se = new Map();
  function ne(t, e, i) {
    return (function(t, e) {
      e = e || {};
      const i = t + JSON.stringify(e);
      let s = se.get(i);
      return (s || ((s = new Intl.NumberFormat(t, e)), se.set(i, s)), s);
    })(e, i).format(t);
  }
  const oe = {
    values: t => (n(t) ? t : `${t}`),
    numeric(t, e, i) {
      if (t === 0) {
        return '0';
      }
      const s = this.chart.options.locale;
      let n;
      let o = t;
      if (i.length > 1) {
        const e = Math.max(
          Math.abs(i[0].value),
          Math.abs(i[i.length - 1].value),
        );
        ((e < 1e-4 || e > 1e15) && (n = 'scientific'),
        (o = (function(t, e) {
          let i =
              e.length > 3 ? e[2].value - e[1].value : e[1].value - e[0].value;
          Math.abs(i) >= 1 && t !== Math.floor(t) && (i = t - Math.floor(t));
          return i;
        })(t, i)));
      }
      const a = z(Math.abs(o));
      const r = isNaN(a) ? 1 : Math.max(Math.min(-1 * Math.floor(a), 20), 0);
      const l = { notation: n, minimumFractionDigits: r, maximumFractionDigits: r };
      return (Object.assign(l, this.options.ticks.format), ne(t, s, l));
    },
    logarithmic(t, e, i) {
      if (t === 0) {
        return '0';
      }
      const s = i[e].significand || t / Math.pow(10, Math.floor(z(t)));
      return [1, 2, 3, 5, 10, 15].includes(s) || e > 0.8 * i.length ?
        oe.numeric.call(this, t, e, i) :
        '';
    },
  };
  const ae = { formatters: oe };
  const re = Object.create(null);
  const le = Object.create(null);
  function he(t, e) {
    if (!e) {
      return t;
    }
    const i = e.split('.');
    for (let e = 0, s = i.length; e < s; ++e) {
      const s = i[e];
      t = t[s] || (t[s] = Object.create(null));
    }
    return t;
  }
  function ce(t, e, i) {
    return typeof e === 'string' ? x(he(t, e), i) : x(he(t, ''), e);
  }
  class de {
    constructor(t, e) {
      ((this.animation = void 0),
      (this.backgroundColor = 'rgba(0,0,0,0.1)'),
      (this.borderColor = 'rgba(0,0,0,0.1)'),
      (this.color = '#666'),
      (this.datasets = {}),
      (this.devicePixelRatio = t => t.chart.platform.getDevicePixelRatio()),
      (this.elements = {}),
      (this.events = [
        'mousemove',
        'mouseout',
        'click',
        'touchstart',
        'touchmove',
      ]),
      (this.font = {
        family: '\'Helvetica Neue\', \'Helvetica\', \'Arial\', sans-serif',
        size: 12,
        style: 'normal',
        lineHeight: 1.2,
        weight: null,
      }),
      (this.hover = {}),
      (this.hoverBackgroundColor = (t, e) => te(e.backgroundColor)),
      (this.hoverBorderColor = (t, e) => te(e.borderColor)),
      (this.hoverColor = (t, e) => te(e.color)),
      (this.indexAxis = 'x'),
      (this.interaction = {
        mode: 'nearest',
        intersect: !0,
        includeInvisible: !1,
      }),
      (this.maintainAspectRatio = !0),
      (this.onHover = null),
      (this.onClick = null),
      (this.parsing = !0),
      (this.plugins = {}),
      (this.responsive = !0),
      (this.scale = void 0),
      (this.scales = {}),
      (this.showLine = !0),
      (this.drawActiveElementsOnTop = !0),
      this.describe(t),
      this.apply(e));
    }

    set(t, e) {
      return ce(this, t, e);
    }

    get(t) {
      return he(this, t);
    }

    describe(t, e) {
      return ce(le, t, e);
    }

    override(t, e) {
      return ce(re, t, e);
    }

    route(t, e, i, s) {
      const n = he(this, t);
      const a = he(this, i);
      const r = `_${e}`;
      Object.defineProperties(n, {
        [r]: { value: n[e], writable: !0 },
        [e]: {
          enumerable: !0,
          get() {
            const t = this[r];
            const e = a[s];
            return o(t) ? Object.assign({}, e, t) : l(t, e);
          },
          set(t) {
            this[r] = t;
          },
        },
      });
    }

    apply(t) {
      t.forEach(t => t(this));
    }
  }
  const ue = new de(
    {
      _scriptable: t => !t.startsWith('on'),
      _indexable: t => t !== 'events',
      hover: { _fallback: 'interaction' },
      interaction: { _scriptable: !1, _indexable: !1 },
    },
    [
      function(t) {
        (t.set('animation', {
          delay: void 0,
          duration: 1e3,
          easing: 'easeOutQuart',
          fn: void 0,
          from: void 0,
          loop: void 0,
          to: void 0,
          type: void 0,
        }),
        t.describe('animation', {
          _fallback: !1,
          _indexable: !1,
          _scriptable: t =>
            t !== 'onProgress' && t !== 'onComplete' && t !== 'fn',
        }),
        t.set('animations', {
          colors: { type: 'color', properties: ie },
          numbers: { type: 'number', properties: ee },
        }),
        t.describe('animations', { _fallback: 'animation' }),
        t.set('transitions', {
          active: { animation: { duration: 400 } },
          resize: { animation: { duration: 0 } },
          show: {
            animations: {
              colors: { from: 'transparent' },
              visible: { type: 'boolean', duration: 0 },
            },
          },
          hide: {
            animations: {
              colors: { to: 'transparent' },
              visible: {
                type: 'boolean',
                easing: 'linear',
                fn: t => 0 | t,
              },
            },
          },
        }));
      },
      function(t) {
        t.set('layout', {
          autoPadding: !0,
          padding: { top: 0, right: 0, bottom: 0, left: 0 },
        });
      },
      function(t) {
        (t.set('scale', {
          display: !0,
          offset: !1,
          reverse: !1,
          beginAtZero: !1,
          bounds: 'ticks',
          clip: !0,
          grace: 0,
          grid: {
            display: !0,
            lineWidth: 1,
            drawOnChartArea: !0,
            drawTicks: !0,
            tickLength: 8,
            tickWidth: (t, e) => e.lineWidth,
            tickColor: (t, e) => e.color,
            offset: !1,
          },
          border: { display: !0, dash: [], dashOffset: 0, width: 1 },
          title: { display: !1, text: '', padding: { top: 4, bottom: 4 } },
          ticks: {
            minRotation: 0,
            maxRotation: 50,
            mirror: !1,
            textStrokeWidth: 0,
            textStrokeColor: '',
            padding: 3,
            display: !0,
            autoSkip: !0,
            autoSkipPadding: 3,
            labelOffset: 0,
            callback: ae.formatters.values,
            minor: {},
            major: {},
            align: 'center',
            crossAlign: 'near',
            showLabelBackdrop: !1,
            backdropColor: 'rgba(255, 255, 255, 0.75)',
            backdropPadding: 2,
          },
        }),
        t.route('scale.ticks', 'color', '', 'color'),
        t.route('scale.grid', 'color', '', 'borderColor'),
        t.route('scale.border', 'color', '', 'borderColor'),
        t.route('scale.title', 'color', '', 'color'),
        t.describe('scale', {
          _fallback: !1,
          _scriptable: t =>
            !t.startsWith('before') &&
              !t.startsWith('after') &&
              t !== 'callback' &&
              t !== 'parser',
          _indexable: t =>
            t !== 'borderDash' && t !== 'tickBorderDash' && t !== 'dash',
        }),
        t.describe('scales', { _fallback: 'scale' }),
        t.describe('scale.ticks', {
          _scriptable: t => t !== 'backdropPadding' && t !== 'callback',
          _indexable: t => t !== 'backdropPadding',
        }));
      },
    ],
  );
  function fe() {
    return typeof window !== 'undefined' && typeof document !== 'undefined';
  }
  function ge(t) {
    let e = t.parentNode;
    return (e && e.toString() === '[object ShadowRoot]' && (e = e.host), e);
  }
  function pe(t, e, i) {
    let s;
    return (
      typeof t === 'string' ?
        ((s = parseInt(t, 10)),
        t.indexOf('%') !== -1 && (s = (s / 100) * e.parentNode[i])) :
        (s = t),
      s
    );
  }
  const me = t => t.ownerDocument.defaultView.getComputedStyle(t, null);
  function xe(t, e) {
    return me(t).getPropertyValue(e);
  }
  const be = ['top', 'right', 'bottom', 'left'];
  function _e(t, e, i) {
    const s = {};
    i = i ? `-${i}` : '';
    for (let n = 0; n < 4; n++) {
      const o = be[n];
      s[o] = parseFloat(t[`${e}-${o}${i}`]) || 0;
    }
    return ((s.width = s.left + s.right), (s.height = s.top + s.bottom), s);
  }
  const ye = (t, e, i) => (t > 0 || e > 0) && (!i || !i.shadowRoot);
  function ve(t, e) {
    if ('native' in t) {
      return t;
    }
    const { canvas: i, currentDevicePixelRatio: s } = e;
    const n = me(i);
    const o = n.boxSizing === 'border-box';
    const a = _e(n, 'padding');
    const r = _e(n, 'border', 'width');
    const {
      x: l,
      y: h,
      box: c,
    } = (function(t, e) {
      const i = t.touches;
      const s = i && i.length ? i[0] : t;
      const { offsetX: n, offsetY: o } = s;
      let a;
      let r;
      let l = !1;
      if (ye(n, o, t.target)) {
        ((a = n), (r = o));
      } else {
        const t = e.getBoundingClientRect();
        ((a = s.clientX - t.left), (r = s.clientY - t.top), (l = !0));
      }
      return { x: a, y: r, box: l };
    })(t, i);
    const d = a.left + (c && r.left);
    const u = a.top + (c && r.top);
    let { width: f, height: g } = e;
    return (
      o && ((f -= a.width + r.width), (g -= a.height + r.height)),
      {
        x: Math.round((((l - d) / f) * i.width) / s),
        y: Math.round((((h - u) / g) * i.height) / s),
      }
    );
  }
  const Me = t => Math.round(10 * t) / 10;
  function we(t, e, i, s) {
    const n = me(t);
    const o = _e(n, 'margin');
    const a = pe(n.maxWidth, t, 'clientWidth') || T;
    const r = pe(n.maxHeight, t, 'clientHeight') || T;
    const l = (function(t, e, i) {
      let s, n;
      if (void 0 === e || void 0 === i) {
        const o = t && ge(t);
        if (o) {
          const t = o.getBoundingClientRect();
          const a = me(o);
          const r = _e(a, 'border', 'width');
          const l = _e(a, 'padding');
          ((e = t.width - l.width - r.width),
          (i = t.height - l.height - r.height),
          (s = pe(a.maxWidth, o, 'clientWidth')),
          (n = pe(a.maxHeight, o, 'clientHeight')));
        } else {
          ((e = t.clientWidth), (i = t.clientHeight));
        }
      }
      return { width: e, height: i, maxWidth: s || T, maxHeight: n || T };
    })(t, e, i);
    let { width: h, height: c } = l;
    if (n.boxSizing === 'content-box') {
      const t = _e(n, 'border', 'width');
      const e = _e(n, 'padding');
      ((h -= e.width + t.width), (c -= e.height + t.height));
    }
    ((h = Math.max(0, h - o.width)),
    (c = Math.max(0, s ? h / s : c - o.height)),
    (h = Me(Math.min(h, a, l.maxWidth))),
    (c = Me(Math.min(c, r, l.maxHeight))),
    h && !c && (c = Me(h / 2)));
    return (
      (void 0 !== e || void 0 !== i) &&
        s &&
        l.height &&
        c > l.height &&
        ((c = l.height), (h = Me(Math.floor(c * s)))),
      { width: h, height: c }
    );
  }
  function ke(t, e, i) {
    const s = e || 1;
    const n = Math.floor(t.height * s);
    const o = Math.floor(t.width * s);
    ((t.height = Math.floor(t.height)), (t.width = Math.floor(t.width)));
    const a = t.canvas;
    return (
      a.style &&
        (i || (!a.style.height && !a.style.width)) &&
        ((a.style.height = `${t.height}px`), (a.style.width = `${t.width}px`)),
      (t.currentDevicePixelRatio !== s || a.height !== n || a.width !== o) &&
        ((t.currentDevicePixelRatio = s),
        (a.height = n),
        (a.width = o),
        t.ctx.setTransform(s, 0, 0, s, 0, 0),
        !0)
    );
  }
  const Se = (function() {
    let t = !1;
    try {
      const e = {
        get passive() {
          return ((t = !0), !1);
        },
      };
      fe() &&
        (window.addEventListener('test', null, e),
        window.removeEventListener('test', null, e));
    } catch (t) {}
    return t;
  })();
  function Pe(t, e) {
    const i = xe(t, e);
    const s = i && i.match(/^(\d+)(\.\d+)?px$/);
    return s ? +s[1] : void 0;
  }
  function De(t) {
    return !t || s(t.size) || s(t.family) ?
      null :
      `${(t.style ? `${t.style} ` : '') +
          (t.weight ? `${t.weight} ` : '') +
          t.size
      }px ${
        t.family}`;
  }
  function Ce(t, e, i, s, n) {
    let o = e[n];
    return (
      o || ((o = e[n] = t.measureText(n).width), i.push(n)),
      o > s && (s = o),
      s
    );
  }
  function Oe(t, e, i, s) {
    let o = ((s = s || {}).data = s.data || {});
    let a = (s.garbageCollect = s.garbageCollect || []);
    (s.font !== e &&
      ((o = s.data = {}), (a = s.garbageCollect = []), (s.font = e)),
    t.save(),
    (t.font = e));
    let r = 0;
    const l = i.length;
    let h, c, d, u, f;
    for (h = 0; h < l; h++) {
      if (((u = i[h]), u == null || n(u))) {
        if (n(u)) {
          for (c = 0, d = u.length; c < d; c++) {
            ((f = u[c]), f == null || n(f) || (r = Ce(t, o, a, r, f)));
          }
        }
      } else {
        r = Ce(t, o, a, r, u);
      }
    }
    t.restore();
    const g = a.length / 2;
    if (g > i.length) {
      for (h = 0; h < g; h++) {
        delete o[a[h]];
      }
      a.splice(0, g);
    }
    return r;
  }
  function Ae(t, e, i) {
    const s = t.currentDevicePixelRatio;
    const n = i !== 0 ? Math.max(i / 2, 0.5) : 0;
    return Math.round((e - n) * s) / s + n;
  }
  function Te(t, e) {
    (e || t) &&
      ((e = e || t.getContext('2d')).save(),
      e.resetTransform(),
      e.clearRect(0, 0, t.width, t.height),
      e.restore());
  }
  function Le(t, e, i, s) {
    Ee(t, e, i, s, null);
  }
  function Ee(t, e, i, s, n) {
    let o, a, r, l, h, c, d, u;
    const f = e.pointStyle;
    const g = e.rotation;
    const p = e.radius;
    let m = (g || 0) * L;
    if (
      f &&
      typeof f === 'object' &&
      ((o = f.toString()),
      o === '[object HTMLImageElement]' || o === '[object HTMLCanvasElement]')
    ) {
      return (
        t.save(),
        t.translate(i, s),
        t.rotate(m),
        t.drawImage(f, -f.width / 2, -f.height / 2, f.width, f.height),
        void t.restore()
      );
    }
    if (!(isNaN(p) || p <= 0)) {
      switch ((t.beginPath(), f)) {
      default:
        (n ? t.ellipse(i, s, n / 2, p, 0, 0, O) : t.arc(i, s, p, 0, O),
        t.closePath());
        break;
      case 'triangle':
        ((c = n ? n / 2 : p),
        t.moveTo(i + Math.sin(m) * c, s - Math.cos(m) * p),
        (m += I),
        t.lineTo(i + Math.sin(m) * c, s - Math.cos(m) * p),
        (m += I),
        t.lineTo(i + Math.sin(m) * c, s - Math.cos(m) * p),
        t.closePath());
        break;
      case 'rectRounded':
        ((h = 0.516 * p),
        (l = p - h),
        (a = Math.cos(m + R) * l),
        (d = Math.cos(m + R) * (n ? n / 2 - h : l)),
        (r = Math.sin(m + R) * l),
        (u = Math.sin(m + R) * (n ? n / 2 - h : l)),
        t.arc(i - d, s - r, h, m - C, m - E),
        t.arc(i + u, s - a, h, m - E, m),
        t.arc(i + d, s + r, h, m, m + E),
        t.arc(i - u, s + a, h, m + E, m + C),
        t.closePath());
        break;
      case 'rect':
        if (!g) {
          ((l = Math.SQRT1_2 * p),
          (c = n ? n / 2 : l),
          t.rect(i - c, s - l, 2 * c, 2 * l));
          break;
        }
        m += R;
      case 'rectRot':
        ((d = Math.cos(m) * (n ? n / 2 : p)),
        (a = Math.cos(m) * p),
        (r = Math.sin(m) * p),
        (u = Math.sin(m) * (n ? n / 2 : p)),
        t.moveTo(i - d, s - r),
        t.lineTo(i + u, s - a),
        t.lineTo(i + d, s + r),
        t.lineTo(i - u, s + a),
        t.closePath());
        break;
      case 'crossRot':
        m += R;
      case 'cross':
        ((d = Math.cos(m) * (n ? n / 2 : p)),
        (a = Math.cos(m) * p),
        (r = Math.sin(m) * p),
        (u = Math.sin(m) * (n ? n / 2 : p)),
        t.moveTo(i - d, s - r),
        t.lineTo(i + d, s + r),
        t.moveTo(i + u, s - a),
        t.lineTo(i - u, s + a));
        break;
      case 'star':
        ((d = Math.cos(m) * (n ? n / 2 : p)),
        (a = Math.cos(m) * p),
        (r = Math.sin(m) * p),
        (u = Math.sin(m) * (n ? n / 2 : p)),
        t.moveTo(i - d, s - r),
        t.lineTo(i + d, s + r),
        t.moveTo(i + u, s - a),
        t.lineTo(i - u, s + a),
        (m += R),
        (d = Math.cos(m) * (n ? n / 2 : p)),
        (a = Math.cos(m) * p),
        (r = Math.sin(m) * p),
        (u = Math.sin(m) * (n ? n / 2 : p)),
        t.moveTo(i - d, s - r),
        t.lineTo(i + d, s + r),
        t.moveTo(i + u, s - a),
        t.lineTo(i - u, s + a));
        break;
      case 'line':
        ((a = n ? n / 2 : Math.cos(m) * p),
        (r = Math.sin(m) * p),
        t.moveTo(i - a, s - r),
        t.lineTo(i + a, s + r));
        break;
      case 'dash':
        (t.moveTo(i, s),
        t.lineTo(i + Math.cos(m) * (n ? n / 2 : p), s + Math.sin(m) * p));
        break;
      case !1:
        t.closePath();
      }
      (t.fill(), e.borderWidth > 0 && t.stroke());
    }
  }
  function Re(t, e, i) {
    return (
      (i = i || 0.5),
      !e ||
        (t &&
          t.x > e.left - i &&
          t.x < e.right + i &&
          t.y > e.top - i &&
          t.y < e.bottom + i)
    );
  }
  function Ie(t, e) {
    (t.save(),
    t.beginPath(),
    t.rect(e.left, e.top, e.right - e.left, e.bottom - e.top),
    t.clip());
  }
  function ze(t) {
    t.restore();
  }
  function Fe(t, e, i, s, n) {
    if (!e) {
      return t.lineTo(i.x, i.y);
    }
    if (n === 'middle') {
      const s = (e.x + i.x) / 2;
      (t.lineTo(s, e.y), t.lineTo(s, i.y));
    } else {
      (n === 'after') != !!s ? t.lineTo(e.x, i.y) : t.lineTo(i.x, e.y);
    }
    t.lineTo(i.x, i.y);
  }
  function Ve(t, e, i, s) {
    if (!e) {
      return t.lineTo(i.x, i.y);
    }
    t.bezierCurveTo(
      s ? e.cp1x : e.cp2x,
      s ? e.cp1y : e.cp2y,
      s ? i.cp2x : i.cp1x,
      s ? i.cp2y : i.cp1y,
      i.x,
      i.y,
    );
  }
  function Be(t, e, i, s, n) {
    if (n.strikethrough || n.underline) {
      const o = t.measureText(s);
      const a = e - o.actualBoundingBoxLeft;
      const r = e + o.actualBoundingBoxRight;
      const l = i - o.actualBoundingBoxAscent;
      const h = i + o.actualBoundingBoxDescent;
      const c = n.strikethrough ? (l + h) / 2 : h;
      ((t.strokeStyle = t.fillStyle),
      t.beginPath(),
      (t.lineWidth = n.decorationWidth || 2),
      t.moveTo(a, c),
      t.lineTo(r, c),
      t.stroke());
    }
  }
  function We(t, e) {
    const i = t.fillStyle;
    ((t.fillStyle = e.color),
    t.fillRect(e.left, e.top, e.width, e.height),
    (t.fillStyle = i));
  }
  function Ne(t, e, i, o, a, r = {}) {
    const l = n(e) ? e : [e];
    const h = r.strokeWidth > 0 && r.strokeColor !== '';
    let c, d;
    for (
      t.save(),
      t.font = a.string,
      (function(t, e) {
        (e.translation && t.translate(e.translation[0], e.translation[1]),
        s(e.rotation) || t.rotate(e.rotation),
        e.color && (t.fillStyle = e.color),
        e.textAlign && (t.textAlign = e.textAlign),
        e.textBaseline && (t.textBaseline = e.textBaseline));
      })(t, r),
      c = 0;
      c < l.length;
      ++c
    ) {
      ((d = l[c]),
      r.backdrop && We(t, r.backdrop),
      h &&
          (r.strokeColor && (t.strokeStyle = r.strokeColor),
          s(r.strokeWidth) || (t.lineWidth = r.strokeWidth),
          t.strokeText(d, i, o, r.maxWidth)),
      t.fillText(d, i, o, r.maxWidth),
      Be(t, i, o, d, r),
      (o += Number(a.lineHeight)));
    }
    t.restore();
  }
  function He(t, e) {
    const { x: i, y: s, w: n, h: o, radius: a } = e;
    (t.arc(i + a.topLeft, s + a.topLeft, a.topLeft, 1.5 * C, C, !0),
    t.lineTo(i, s + o - a.bottomLeft),
    t.arc(i + a.bottomLeft, s + o - a.bottomLeft, a.bottomLeft, C, E, !0),
    t.lineTo(i + n - a.bottomRight, s + o),
    t.arc(
      i + n - a.bottomRight,
      s + o - a.bottomRight,
      a.bottomRight,
      E,
      0,
      !0,
    ),
    t.lineTo(i + n, s + a.topRight),
    t.arc(i + n - a.topRight, s + a.topRight, a.topRight, 0, -E, !0),
    t.lineTo(i + a.topLeft, s));
  }
  function je(t, e = [''], i, s, n = () => t[0]) {
    const o = i || t;
    void 0 === s && (s = ti('_fallback', t));
    const a = {
      [Symbol.toStringTag]: 'Object',
      _cacheable: !0,
      _scopes: t,
      _rootScopes: o,
      _fallback: s,
      _getTarget: n,
      override: i => je([i, ...t], e, o, s),
    };
    return new Proxy(a, {
      deleteProperty: (e, i) => (
        delete e[i],
        delete e._keys,
        delete t[0][i],
        !0
      ),
      get: (i, s) =>
        qe(i, s, () =>
          (function(t, e, i, s) {
            let n;
            for (const o of e) {
              if (((n = ti(Ue(o, t), i)), void 0 !== n)) {
                return Xe(t, n) ? Ze(i, s, t, n) : n;
              }
            }
          })(s, e, t, i),
        ),
      getOwnPropertyDescriptor: (t, e) =>
        Reflect.getOwnPropertyDescriptor(t._scopes[0], e),
      getPrototypeOf: () => Reflect.getPrototypeOf(t[0]),
      has: (t, e) => ei(t).includes(e),
      ownKeys: t => ei(t),
      set(t, e, i) {
        const s = t._storage || (t._storage = n());
        return ((t[e] = s[e] = i), delete t._keys, !0);
      },
    });
  }
  function $e(t, e, i, s) {
    const a = {
      _cacheable: !1,
      _proxy: t,
      _context: e,
      _subProxy: i,
      _stack: new Set(),
      _descriptors: Ye(t, s),
      setContext: e => $e(t, e, i, s),
      override: n => $e(t.override(n), e, i, s),
    };
    return new Proxy(a, {
      deleteProperty: (e, i) => (delete e[i], delete t[i], !0),
      get: (t, e, i) =>
        qe(t, e, () =>
          (function(t, e, i) {
            const { _proxy: s, _context: a, _subProxy: r, _descriptors: l } = t;
            let h = s[e];
            S(h) &&
              l.isScriptable(e) &&
              (h = (function(t, e, i, s) {
                const { _proxy: n, _context: o, _subProxy: a, _stack: r } = i;
                if (r.has(t)) {
                  throw new Error(
                    `Recursion detected: ${
                      Array.from(r).join('->')
                    }->${
                      t}`,
                  );
                }
                r.add(t);
                let l = e(o, a || s);
                (r.delete(t), Xe(t, l) && (l = Ze(n._scopes, n, t, l)));
                return l;
              })(e, h, t, i));
            n(h) &&
              h.length &&
              (h = (function(t, e, i, s) {
                const {
                  _proxy: n,
                  _context: a,
                  _subProxy: r,
                  _descriptors: l,
                } = i;
                if (void 0 !== a.index && s(t)) {
                  return e[a.index % e.length];
                }
                if (o(e[0])) {
                  const i = e;
                  const s = n._scopes.filter(t => t !== i);
                  e = [];
                  for (const o of i) {
                    const i = Ze(s, n, t, o);
                    e.push($e(i, a, r && r[t], l));
                  }
                }
                return e;
              })(e, h, t, l.isIndexable));
            Xe(e, h) && (h = $e(h, a, r && r[e], l));
            return h;
          })(t, e, i),
        ),
      getOwnPropertyDescriptor: (e, i) =>
        e._descriptors.allKeys ?
          Reflect.has(t, i) ?
            { enumerable: !0, configurable: !0 } :
            void 0 :
          Reflect.getOwnPropertyDescriptor(t, i),
      getPrototypeOf: () => Reflect.getPrototypeOf(t),
      has: (e, i) => Reflect.has(t, i),
      ownKeys: () => Reflect.ownKeys(t),
      set: (e, i, s) => ((t[i] = s), delete e[i], !0),
    });
  }
  function Ye(t, e = { scriptable: !0, indexable: !0 }) {
    const {
      _scriptable: i = e.scriptable,
      _indexable: s = e.indexable,
      _allKeys: n = e.allKeys,
    } = t;
    return {
      allKeys: n,
      scriptable: i,
      indexable: s,
      isScriptable: S(i) ? i : () => i,
      isIndexable: S(s) ? s : () => s,
    };
  }
  const Ue = (t, e) => (t ? t + w(e) : e);
  const Xe = (t, e) =>
    o(e) &&
      t !== 'adapters' &&
      (Object.getPrototypeOf(e) === null || e.constructor === Object);
  function qe(t, e, i) {
    if (Object.prototype.hasOwnProperty.call(t, e) || e === 'constructor') {
      return t[e];
    }
    const s = i();
    return ((t[e] = s), s);
  }
  function Ke(t, e, i) {
    return S(t) ? t(e, i) : t;
  }
  const Ge = (t, e) => (!0 === t ? e : typeof t === 'string' ? M(e, t) : void 0);
  function Je(t, e, i, s, n) {
    for (const o of e) {
      const e = Ge(i, o);
      if (e) {
        t.add(e);
        const o = Ke(e._fallback, i, n);
        if (void 0 !== o && o !== i && o !== s) {
          return o;
        }
      } else if (!1 === e && void 0 !== s && i !== s) {
        return null;
      }
    }
    return !1;
  }
  function Ze(t, e, i, s) {
    const a = e._rootScopes;
    const r = Ke(e._fallback, i, s);
    const l = [...t, ...a];
    const h = new Set();
    h.add(s);
    let c = Qe(h, l, i, r || i, s);
    return (
      c !== null &&
      (void 0 === r || r === i || ((c = Qe(h, l, r, c, s)), c !== null)) &&
      je(Array.from(h), [''], a, r, () =>
        (function(t, e, i) {
          const s = t._getTarget();
          e in s || (s[e] = {});
          const a = s[e];
          if (n(a) && o(i)) {
            return i;
          }
          return a || {};
        })(e, i, s),
      )
    );
  }
  function Qe(t, e, i, s, n) {
    for (; i;) {
      i = Je(t, e, i, s, n);
    }
    return i;
  }
  function ti(t, e) {
    for (const i of e) {
      if (!i) {
        continue;
      }
      const e = i[t];
      if (void 0 !== e) {
        return e;
      }
    }
  }
  function ei(t) {
    let e = t._keys;
    return (
      e ||
        (e = t._keys =
          (function(t) {
            const e = new Set();
            for (const i of t) {
              for (const t of Object.keys(i).filter(t => !t.startsWith('_'))) {
                e.add(t);
              }
            }
            return Array.from(e);
          })(t._scopes)),
      e
    );
  }
  function ii(t, e, i, s) {
    const { iScale: n } = t;
    const { key: o = 'r' } = this._parsing;
    const a = new Array(s);
    let r, l, h, c;
    for (r = 0, l = s; r < l; ++r) {
      ((h = r + i), (c = e[h]), (a[r] = { r: n.parse(M(c, o), h) }));
    }
    return a;
  }
  const si = Number.EPSILON || 1e-14;
  const ni = (t, e) => e < t.length && !t[e].skip && t[e];
  const oi = t => (t === 'x' ? 'y' : 'x');
  function ai(t, e, i, s) {
    const n = t.skip ? e : t;
    const o = e;
    const a = i.skip ? e : i;
    const r = q(o, n);
    const l = q(a, o);
    let h = r / (r + l);
    let c = l / (r + l);
    ((h = isNaN(h) ? 0 : h), (c = isNaN(c) ? 0 : c));
    const d = s * h;
    const u = s * c;
    return {
      previous: { x: o.x - d * (a.x - n.x), y: o.y - d * (a.y - n.y) },
      next: { x: o.x + u * (a.x - n.x), y: o.y + u * (a.y - n.y) },
    };
  }
  function ri(t, e = 'x') {
    const i = oi(e);
    const s = t.length;
    const n = Array(s).fill(0);
    const o = Array(s);
    let a;
    let r;
    let l;
    let h = ni(t, 0);
    for (a = 0; a < s; ++a) {
      if (((r = l), (l = h), (h = ni(t, a + 1)), l)) {
        if (h) {
          const t = h[e] - l[e];
          n[a] = t !== 0 ? (h[i] - l[i]) / t : 0;
        }
        o[a] = r ?
          h ?
            F(n[a - 1]) !== F(n[a]) ?
              0 :
              (n[a - 1] + n[a]) / 2 :
            n[a - 1] :
          n[a];
      }
    }
    (!(function(t, e, i) {
      const s = t.length;
      let n;
      let o;
      let a;
      let r;
      let l;
      let h = ni(t, 0);
      for (let c = 0; c < s - 1; ++c) {
        ((l = h),
        (h = ni(t, c + 1)),
        l &&
            h &&
            (V(e[c], 0, si) ?
              (i[c] = i[c + 1] = 0) :
              ((n = i[c] / e[c]),
              (o = i[c + 1] / e[c]),
              (r = Math.pow(n, 2) + Math.pow(o, 2)),
              r <= 9 ||
                  ((a = 3 / Math.sqrt(r)),
                  (i[c] = n * a * e[c]),
                  (i[c + 1] = o * a * e[c])))));
      }
    })(t, n, o),
    (function(t, e, i = 'x') {
      const s = oi(i);
      const n = t.length;
      let o;
      let a;
      let r;
      let l = ni(t, 0);
      for (let h = 0; h < n; ++h) {
        if (((a = r), (r = l), (l = ni(t, h + 1)), !r)) {
          continue;
        }
        const n = r[i];
        const c = r[s];
        (a &&
            ((o = (n - a[i]) / 3),
            (r[`cp1${i}`] = n - o),
            (r[`cp1${s}`] = c - o * e[h])),
        l &&
              ((o = (l[i] - n) / 3),
              (r[`cp2${i}`] = n + o),
              (r[`cp2${s}`] = c + o * e[h])));
      }
    })(t, o, e));
  }
  function li(t, e, i) {
    return Math.max(Math.min(t, i), e);
  }
  function hi(t, e, i, s, n) {
    let o, a, r, l;
    if (
      (e.spanGaps && (t = t.filter(t => !t.skip)),
      e.cubicInterpolationMode === 'monotone')
    ) {
      ri(t, n);
    } else {
      let i = s ? t[t.length - 1] : t[0];
      for (o = 0, a = t.length; o < a; ++o) {
        ((r = t[o]),
        (l = ai(i, r, t[Math.min(o + 1, a - (s ? 0 : 1)) % a], e.tension)),
        (r.cp1x = l.previous.x),
        (r.cp1y = l.previous.y),
        (r.cp2x = l.next.x),
        (r.cp2y = l.next.y),
        (i = r));
      }
    }
    e.capBezierPoints &&
      (function(t, e) {
        let i;
        let s;
        let n;
        let o;
        let a;
        let r = Re(t[0], e);
        for (i = 0, s = t.length; i < s; ++i) {
          ((a = o),
          (o = r),
          (r = i < s - 1 && Re(t[i + 1], e)),
          o &&
              ((n = t[i]),
              a &&
                ((n.cp1x = li(n.cp1x, e.left, e.right)),
                (n.cp1y = li(n.cp1y, e.top, e.bottom))),
              r &&
                ((n.cp2x = li(n.cp2x, e.left, e.right)),
                (n.cp2y = li(n.cp2y, e.top, e.bottom)))));
        }
      })(t, i);
  }
  const ci = t => t === 0 || t === 1;
  const di = (t, e, i) => -Math.pow(2, 10 * (t -= 1)) * Math.sin(((t - e) * O) / i);
  const ui = (t, e, i) => Math.pow(2, -10 * t) * Math.sin(((t - e) * O) / i) + 1;
  const fi = {
    linear: t => t,
    easeInQuad: t => t * t,
    easeOutQuad: t => -t * (t - 2),
    easeInOutQuad: t =>
      (t /= 0.5) < 1 ? 0.5 * t * t : -0.5 * (--t * (t - 2) - 1),
    easeInCubic: t => t * t * t,
    easeOutCubic: t => (t -= 1) * t * t + 1,
    easeInOutCubic: t =>
      (t /= 0.5) < 1 ? 0.5 * t * t * t : 0.5 * ((t -= 2) * t * t + 2),
    easeInQuart: t => t * t * t * t,
    easeOutQuart: t => -((t -= 1) * t * t * t - 1),
    easeInOutQuart: t =>
      (t /= 0.5) < 1 ?
        0.5 * t * t * t * t :
        -0.5 * ((t -= 2) * t * t * t - 2),
    easeInQuint: t => t * t * t * t * t,
    easeOutQuint: t => (t -= 1) * t * t * t * t + 1,
    easeInOutQuint: t =>
      (t /= 0.5) < 1 ?
        0.5 * t * t * t * t * t :
        0.5 * ((t -= 2) * t * t * t * t + 2),
    easeInSine: t => 1 - Math.cos(t * E),
    easeOutSine: t => Math.sin(t * E),
    easeInOutSine: t => -0.5 * (Math.cos(C * t) - 1),
    easeInExpo: t => (t === 0 ? 0 : Math.pow(2, 10 * (t - 1))),
    easeOutExpo: t => (t === 1 ? 1 : 1 - Math.pow(2, -10 * t)),
    easeInOutExpo: t =>
      ci(t) ?
        t :
        t < 0.5 ?
          0.5 * Math.pow(2, 10 * (2 * t - 1)) :
          0.5 * (2 - Math.pow(2, -10 * (2 * t - 1))),
    easeInCirc: t => (t >= 1 ? t : -(Math.sqrt(1 - t * t) - 1)),
    easeOutCirc: t => Math.sqrt(1 - (t -= 1) * t),
    easeInOutCirc: t =>
      (t /= 0.5) < 1 ?
        -0.5 * (Math.sqrt(1 - t * t) - 1) :
        0.5 * (Math.sqrt(1 - (t -= 2) * t) + 1),
    easeInElastic: t => (ci(t) ? t : di(t, 0.075, 0.3)),
    easeOutElastic: t => (ci(t) ? t : ui(t, 0.075, 0.3)),
    easeInOutElastic(t) {
      const e = 0.1125;
      return ci(t) ?
        t :
        t < 0.5 ?
          0.5 * di(2 * t, e, 0.45) :
          0.5 + 0.5 * ui(2 * t - 1, e, 0.45);
    },
    easeInBack(t) {
      const e = 1.70158;
      return t * t * ((e + 1) * t - e);
    },
    easeOutBack(t) {
      const e = 1.70158;
      return (t -= 1) * t * ((e + 1) * t + e) + 1;
    },
    easeInOutBack(t) {
      let e = 1.70158;
      return (t /= 0.5) < 1 ?
        t * t * ((1 + (e *= 1.525)) * t - e) * 0.5 :
        0.5 * ((t -= 2) * t * ((1 + (e *= 1.525)) * t + e) + 2);
    },
    easeInBounce: t => 1 - fi.easeOutBounce(1 - t),
    easeOutBounce(t) {
      const e = 7.5625;
      const i = 2.75;
      return t < 1 / i ?
        e * t * t :
        t < 2 / i ?
          e * (t -= 1.5 / i) * t + 0.75 :
          t < 2.5 / i ?
            e * (t -= 2.25 / i) * t + 0.9375 :
            e * (t -= 2.625 / i) * t + 0.984375;
    },
    easeInOutBounce: t =>
      t < 0.5 ?
        0.5 * fi.easeInBounce(2 * t) :
        0.5 * fi.easeOutBounce(2 * t - 1) + 0.5,
  };
  function gi(t, e, i, s) {
    return { x: t.x + i * (e.x - t.x), y: t.y + i * (e.y - t.y) };
  }
  function pi(t, e, i, s) {
    return {
      x: t.x + i * (e.x - t.x),
      y:
        s === 'middle' ?
          i < 0.5 ?
            t.y :
            e.y :
          s === 'after' ?
            i < 1 ?
              t.y :
              e.y :
            i > 0 ?
              e.y :
              t.y,
    };
  }
  function mi(t, e, i, s) {
    const n = { x: t.cp2x, y: t.cp2y };
    const o = { x: e.cp1x, y: e.cp1y };
    const a = gi(t, n, i);
    const r = gi(n, o, i);
    const l = gi(o, e, i);
    const h = gi(a, r, i);
    const c = gi(r, l, i);
    return gi(h, c, i);
  }
  const xi = /^(normal|(\d+(?:\.\d+)?)(px|em|%)?)$/;
  const bi =
      /^(normal|italic|initial|inherit|unset|(oblique( -?[0-9]?[0-9]deg)?))$/;
  function _i(t, e) {
    const i = (`${t}`).match(xi);
    if (!i || i[1] === 'normal') {
      return 1.2 * e;
    }
    switch (((t = +i[2]), i[3])) {
    case 'px':
      return t;
    case '%':
      t /= 100;
    }
    return e * t;
  }
  const yi = t => +t || 0;
  function vi(t, e) {
    const i = {};
    const s = o(e);
    const n = s ? Object.keys(e) : e;
    const a = o(t) ? (s ? i => l(t[i], t[e[i]]) : e => t[e]) : () => t;
    for (const t of n) {
      i[t] = yi(a(t));
    }
    return i;
  }
  function Mi(t) {
    return vi(t, { top: 'y', right: 'x', bottom: 'y', left: 'x' });
  }
  function wi(t) {
    return vi(t, ['topLeft', 'topRight', 'bottomLeft', 'bottomRight']);
  }
  function ki(t) {
    const e = Mi(t);
    return ((e.width = e.left + e.right), (e.height = e.top + e.bottom), e);
  }
  function Si(t, e) {
    ((t = t || {}), (e = e || ue.font));
    let i = l(t.size, e.size);
    typeof i === 'string' && (i = parseInt(i, 10));
    let s = l(t.style, e.style);
    s &&
      !(`${s}`).match(bi) &&
      (console.warn(`Invalid font style specified: "${s}"`), (s = void 0));
    const n = {
      family: l(t.family, e.family),
      lineHeight: _i(l(t.lineHeight, e.lineHeight), i),
      size: i,
      style: s,
      weight: l(t.weight, e.weight),
      string: '',
    };
    return ((n.string = De(n)), n);
  }
  function Pi(t, e, i, s) {
    let o;
    let a;
    let r;
    let l = !0;
    for (o = 0, a = t.length; o < a; ++o) {
      if (
        ((r = t[o]),
        void 0 !== r &&
          (void 0 !== e && typeof r === 'function' && ((r = r(e)), (l = !1)),
          void 0 !== i && n(r) && ((r = r[i % r.length]), (l = !1)),
          void 0 !== r))
      ) {
        return (s && !l && (s.cacheable = !1), r);
      }
    }
  }
  function Di(t, e, i) {
    const { min: s, max: n } = t;
    const o = c(e, (n - s) / 2);
    const a = (t, e) => (i && t === 0 ? 0 : t + e);
    return { min: a(s, -Math.abs(o)), max: a(n, o) };
  }
  function Ci(t, e) {
    return Object.assign(Object.create(t), e);
  }
  function Oi(t, e, i) {
    return t ?
      (function(t, e) {
        return {
          x: i => t + t + e - i,
          setWidth(t) {
            e = t;
          },
          textAlign: t =>
            t === 'center' ? t : t === 'right' ? 'left' : 'right',
          xPlus: (t, e) => t - e,
          leftForLtr: (t, e) => t - e,
        };
      })(e, i) :
      {
        x: t => t,
        setWidth(t) {},
        textAlign: t => t,
        xPlus: (t, e) => t + e,
        leftForLtr: (t, e) => t,
      };
  }
  function Ai(t, e) {
    let i, s;
    (e !== 'ltr' && e !== 'rtl') ||
      ((i = t.canvas.style),
      (s = [
        i.getPropertyValue('direction'),
        i.getPropertyPriority('direction'),
      ]),
      i.setProperty('direction', e, 'important'),
      (t.prevTextDirection = s));
  }
  function Ti(t, e) {
    void 0 !== e &&
      (delete t.prevTextDirection,
      t.canvas.style.setProperty('direction', e[0], e[1]));
  }
  function Li(t) {
    return t === 'angle' ?
      { between: J, compare: K, normalize: G } :
      { between: tt, compare: (t, e) => t - e, normalize: t => t };
  }
  function Ei({ start: t, end: e, count: i, loop: s, style: n }) {
    return {
      start: t % i,
      end: e % i,
      loop: s && (e - t + 1) % i == 0,
      style: n,
    };
  }
  function Ri(t, e, i) {
    if (!i) {
      return [t];
    }
    const { property: s, start: n, end: o } = i;
    const a = e.length;
    const { compare: r, between: l, normalize: h } = Li(s);
    const {
      start: c,
      end: d,
      loop: u,
      style: f,
    } = (function(t, e, i) {
      const { property: s, start: n, end: o } = i;
      const { between: a, normalize: r } = Li(s);
      const l = e.length;
      let h;
      let c;
      let { start: d, end: u, loop: f } = t;
      if (f) {
        for (
          d += l, u += l, h = 0, c = l;
          h < c && a(r(e[d % l][s]), n, o);
          ++h
        ) {
          (d--, u--);
        }
        ((d %= l), (u %= l));
      }
      return (
        u < d && (u += l),
        { start: d, end: u, loop: f, style: t.style }
      );
    })(t, e, i);
    const g = [];
    let p;
    let m;
    let x;
    let b = !1;
    let _ = null;
    const y = () => b || (l(n, x, p) && r(n, x) !== 0);
    const v = () => !b || r(o, p) === 0 || l(o, x, p);
    for (let t = c, i = c; t <= d; ++t) {
      ((m = e[t % a]),
      m.skip ||
          ((p = h(m[s])),
          p !== x &&
            ((b = l(p, n, o)),
            _ === null && y() && (_ = r(p, n) === 0 ? t : i),
            _ !== null &&
              v() &&
              (g.push(Ei({ start: _, end: t, loop: u, count: a, style: f })),
              (_ = null)),
            (i = t),
            (x = p))));
    }
    return (
      _ !== null &&
        g.push(Ei({ start: _, end: d, loop: u, count: a, style: f })),
      g
    );
  }
  function Ii(t, e) {
    const i = [];
    const s = t.segments;
    for (let n = 0; n < s.length; n++) {
      const o = Ri(s[n], t.points, e);
      o.length && i.push(...o);
    }
    return i;
  }
  function zi(t, e) {
    const i = t.points;
    const s = t.options.spanGaps;
    const n = i.length;
    if (!n) {
      return [];
    }
    const o = !!t._loop;
    const { start: a, end: r } = (function(t, e, i, s) {
      let n = 0;
      let o = e - 1;
      if (i && !s) {
        for (; n < e && !t[n].skip;) {
          n++;
        }
      }
      for (; n < e && t[n].skip;) {
        n++;
      }
      for (n %= e, i && (o += n); o > n && t[o % e].skip;) {
        o--;
      }
      return ((o %= e), { start: n, end: o });
    })(i, n, o, s);
    if (!0 === s) {
      return Fi(t, [{ start: a, end: r, loop: o }], i, e);
    }
    return Fi(
      t,
      (function(t, e, i, s) {
        const n = t.length;
        const o = [];
        let a;
        let r = e;
        let l = t[e];
        for (a = e + 1; a <= i; ++a) {
          const i = t[a % n];
          (i.skip || i.stop ?
            l.skip ||
              ((s = !1),
              o.push({ start: e % n, end: (a - 1) % n, loop: s }),
              (e = r = i.stop ? a : null)) :
            ((r = a), l.skip && (e = a)),
          (l = i));
        }
        return (r !== null && o.push({ start: e % n, end: r % n, loop: s }), o);
      })(i, a, r < a ? r + n : r, !!t._fullLoop && a === 0 && r === n - 1),
      i,
      e,
    );
  }
  function Fi(t, e, i, s) {
    return s && s.setContext && i ?
      (function(t, e, i, s) {
        const n = t._chart.getContext();
        const o = Vi(t.options);
        const {
          _datasetIndex: a,
          options: { spanGaps: r },
        } = t;
        const l = i.length;
        const h = [];
        let c = o;
        let d = e[0].start;
        let u = d;
        function f(t, e, s, n) {
          const o = r ? -1 : 1;
          if (t !== e) {
            for (t += l; i[t % l].skip;) {
              t -= o;
            }
            for (; i[e % l].skip;) {
              e += o;
            }
            t % l != e % l &&
                (h.push({ start: t % l, end: e % l, loop: s, style: n }),
                (c = n),
                (d = e % l));
          }
        }
        for (const t of e) {
          d = r ? d : t.start;
          let e;
          let o = i[d % l];
          for (u = d + 1; u <= t.end; u++) {
            const r = i[u % l];
            ((e = Vi(
              s.setContext(
                Ci(n, {
                  type: 'segment',
                  p0: o,
                  p1: r,
                  p0DataIndex: (u - 1) % l,
                  p1DataIndex: u % l,
                  datasetIndex: a,
                }),
              ),
            )),
            Bi(e, c) && f(d, u - 1, t.loop, c),
            (o = r),
            (c = e));
          }
          d < u - 1 && f(d, u - 1, t.loop, c);
        }
        return h;
      })(t, e, i, s) :
      e;
  }
  function Vi(t) {
    return {
      backgroundColor: t.backgroundColor,
      borderCapStyle: t.borderCapStyle,
      borderDash: t.borderDash,
      borderDashOffset: t.borderDashOffset,
      borderJoinStyle: t.borderJoinStyle,
      borderWidth: t.borderWidth,
      borderColor: t.borderColor,
    };
  }
  function Bi(t, e) {
    if (!e) {
      return !1;
    }
    const i = [];
    const s = function(t, e) {
      return Zt(e) ? (i.includes(e) || i.push(e), i.indexOf(e)) : e;
    };
    return JSON.stringify(t, s) !== JSON.stringify(e, s);
  }
  function Wi(t, e, i) {
    return t.options.clip ? t[i] : e[i];
  }
  function Ni(t, e) {
    const i = e._clip;
    if (i.disabled) {
      return !1;
    }
    const s = (function(t, e) {
      const { xScale: i, yScale: s } = t;
      return i && s ?
        {
          left: Wi(i, e, 'left'),
          right: Wi(i, e, 'right'),
          top: Wi(s, e, 'top'),
          bottom: Wi(s, e, 'bottom'),
        } :
        e;
    })(e, t.chartArea);
    return {
      left: !1 === i.left ? 0 : s.left - (!0 === i.left ? 0 : i.left),
      right:
        !1 === i.right ? t.width : s.right + (!0 === i.right ? 0 : i.right),
      top: !1 === i.top ? 0 : s.top - (!0 === i.top ? 0 : i.top),
      bottom:
        !1 === i.bottom ?
          t.height :
          s.bottom + (!0 === i.bottom ? 0 : i.bottom),
    };
  }
  const Hi = Object.freeze({
    __proto__: null,
    HALF_PI: E,
    INFINITY: T,
    PI: C,
    PITAU: A,
    QUARTER_PI: R,
    RAD_PER_DEG: L,
    TAU: O,
    TWO_THIRDS_PI: I,
    _addGrace: Di,
    _alignPixel: Ae,
    _alignStartEnd: ft,
    _angleBetween: J,
    _angleDiff: K,
    _arrayUnique: lt,
    _attachContext: $e,
    _bezierCurveTo: Ve,
    _bezierInterpolation: mi,
    _boundSegment: Ri,
    _boundSegments: Ii,
    _capitalize: w,
    _computeSegments: zi,
    _createResolver: je,
    _decimalPlaces: U,
    _deprecated: function(t, e, i, s) {
      void 0 !== e &&
        console.warn(
          `${t}: "${i}" is deprecated. Please use "${s}" instead`,
        );
    },
    _descriptors: Ye,
    _elementsEqual: f,
    _factorize: W,
    _filterBetween: nt,
    _getParentNode: ge,
    _getStartAndCountOfVisiblePoints: pt,
    _int16Range: Q,
    _isBetween: tt,
    _isClickEvent: D,
    _isDomSupported: fe,
    _isPointInArea: Re,
    _limitValue: Z,
    _longestText: Oe,
    _lookup: et,
    _lookupByKey: it,
    _measureText: Ce,
    _merger: m,
    _mergerIf: _,
    _normalizeAngle: G,
    _parseObjectDataRadialScale: ii,
    _pointInLine: gi,
    _readValueToProps: vi,
    _rlookupByKey: st,
    _scaleRangesChanged: mt,
    _setMinAndMaxByKey: j,
    _splitKey: v,
    _steppedInterpolation: pi,
    _steppedLineTo: Fe,
    _textX: gt,
    _toLeftRightCenter: ut,
    _updateBezierControlPoints: hi,
    addRoundedRectPath: He,
    almostEquals: V,
    almostWhole: H,
    callback: d,
    clearCanvas: Te,
    clipArea: Ie,
    clone: g,
    color: Qt,
    createContext: Ci,
    debounce: dt,
    defined: k,
    distanceBetweenPoints: q,
    drawPoint: Le,
    drawPointLegend: Ee,
    each: u,
    easingEffects: fi,
    finiteOrDefault: r,
    fontString: function(t, e, i) {
      return `${e} ${t}px ${i}`;
    },
    formatNumber: ne,
    getAngleFromPoint: X,
    getDatasetClipArea: Ni,
    getHoverColor: te,
    getMaximumSize: we,
    getRelativePosition: ve,
    getRtlAdapter: Oi,
    getStyle: xe,
    isArray: n,
    isFinite: a,
    isFunction: S,
    isNullOrUndef: s,
    isNumber: N,
    isObject: o,
    isPatternOrGradient: Zt,
    listenArrayEvents: at,
    log10: z,
    merge: x,
    mergeIf: b,
    niceNum: B,
    noop: e,
    overrideTextDirection: Ai,
    readUsedSize: Pe,
    renderText: Ne,
    requestAnimFrame: ht,
    resolve: Pi,
    resolveObjectKey: M,
    restoreTextDirection: Ti,
    retinaScale: ke,
    setsEqual: P,
    sign: F,
    splineCurve: ai,
    splineCurveMonotone: ri,
    supportsEventListenerOptions: Se,
    throttled: ct,
    toDegrees: Y,
    toDimension: c,
    toFont: Si,
    toFontString: De,
    toLineHeight: _i,
    toPadding: ki,
    toPercentage: h,
    toRadians: $,
    toTRBL: Mi,
    toTRBLCorners: wi,
    uid: i,
    unclipArea: ze,
    unlistenArrayEvents: rt,
    valueOrDefault: l,
  });
  function ji(t, e, i, n) {
    const { controller: o, data: a, _sorted: r } = t;
    const l = o._cachedMeta.iScale;
    const h = t.dataset && t.dataset.options ? t.dataset.options.spanGaps : null;
    if (l && e === l.axis && e !== 'r' && r && a.length) {
      const r = l._reversePixels ? st : it;
      if (!n) {
        const n = r(a, e, i);
        if (h) {
          const { vScale: e } = o._cachedMeta;
          const { _parsed: i } = t;
          const a = i
            .slice(0, n.lo + 1)
            .reverse()
            .findIndex(t => !s(t[e.axis]));
          n.lo -= Math.max(0, a);
          const r = i.slice(n.hi).findIndex(t => !s(t[e.axis]));
          n.hi += Math.max(0, r);
        }
        return n;
      }
      if (o._sharedOptions) {
        const t = a[0];
        const s = typeof t.getRange === 'function' && t.getRange(e);
        if (s) {
          const t = r(a, e, i - s);
          const n = r(a, e, i + s);
          return { lo: t.lo, hi: n.hi };
        }
      }
    }
    return { lo: 0, hi: a.length - 1 };
  }
  function $i(t, e, i, s, n) {
    const o = t.getSortedVisibleDatasetMetas();
    const a = i[e];
    for (let t = 0, i = o.length; t < i; ++t) {
      const { index: i, data: r } = o[t];
      const { lo: l, hi: h } = ji(o[t], e, a, n);
      for (let t = l; t <= h; ++t) {
        const e = r[t];
        e.skip || s(e, i, t);
      }
    }
  }
  function Yi(t, e, i, s, n) {
    const o = [];
    if (!n && !t.isPointInArea(e)) {
      return o;
    }
    return (
      $i(
        t,
        i,
        e,
        (i, a, r) => {
          (n || Re(i, t.chartArea, 0)) &&
            i.inRange(e.x, e.y, s) &&
            o.push({ element: i, datasetIndex: a, index: r });
        },
        !0,
      ),
      o
    );
  }
  function Ui(t, e, i, s, n, o) {
    let a = [];
    const r = (function(t) {
      const e = t.indexOf('x') !== -1;
      const i = t.indexOf('y') !== -1;
      return function(t, s) {
        const n = e ? Math.abs(t.x - s.x) : 0;
        const o = i ? Math.abs(t.y - s.y) : 0;
        return Math.sqrt(Math.pow(n, 2) + Math.pow(o, 2));
      };
    })(i);
    let l = Number.POSITIVE_INFINITY;
    return (
      $i(t, i, e, (i, h, c) => {
        const d = i.inRange(e.x, e.y, n);
        if (s && !d) {
          return;
        }
        const u = i.getCenterPoint(n);
        if (!(!!o || t.isPointInArea(u)) && !d) {
          return;
        }
        const f = r(e, u);
        f < l ?
          ((a = [{ element: i, datasetIndex: h, index: c }]), (l = f)) :
          f === l && a.push({ element: i, datasetIndex: h, index: c });
      }),
      a
    );
  }
  function Xi(t, e, i, s, n, o) {
    return o || t.isPointInArea(e) ?
      i !== 'r' || s ?
        Ui(t, e, i, s, n, o) :
        (function(t, e, i, s) {
          const n = [];
          return (
            $i(t, i, e, (t, i, o) => {
              const { startAngle: a, endAngle: r } = t.getProps(
                ['startAngle', 'endAngle'],
                s,
              );
              const { angle: l } = X(t, { x: e.x, y: e.y });
              J(l, a, r) && n.push({ element: t, datasetIndex: i, index: o });
            }),
            n
          );
        })(t, e, i, n) :
      [];
  }
  function qi(t, e, i, s, n) {
    const o = [];
    const a = i === 'x' ? 'inXRange' : 'inYRange';
    let r = !1;
    return (
      $i(t, i, e, (t, s, l) => {
        t[a] &&
          t[a](e[i], n) &&
          (o.push({ element: t, datasetIndex: s, index: l }),
          (r = r || t.inRange(e.x, e.y, n)));
      }),
      s && !r ? [] : o
    );
  }
  const Ki = {
    evaluateInteractionItems: $i,
    modes: {
      index(t, e, i, s) {
        const n = ve(e, t);
        const o = i.axis || 'x';
        const a = i.includeInvisible || !1;
        const r = i.intersect ? Yi(t, n, o, s, a) : Xi(t, n, o, !1, s, a);
        const l = [];
        return r.length ?
          (t.getSortedVisibleDatasetMetas().forEach(t => {
            const e = r[0].index;
            const i = t.data[e];
            i &&
                !i.skip &&
                l.push({ element: i, datasetIndex: t.index, index: e });
          }),
          l) :
          [];
      },
      dataset(t, e, i, s) {
        const n = ve(e, t);
        const o = i.axis || 'xy';
        const a = i.includeInvisible || !1;
        let r = i.intersect ? Yi(t, n, o, s, a) : Xi(t, n, o, !1, s, a);
        if (r.length > 0) {
          const e = r[0].datasetIndex;
          const i = t.getDatasetMeta(e).data;
          r = [];
          for (let t = 0; t < i.length; ++t) {
            r.push({ element: i[t], datasetIndex: e, index: t });
          }
        }
        return r;
      },
      point: (t, e, i, s) =>
        Yi(t, ve(e, t), i.axis || 'xy', s, i.includeInvisible || !1),
      nearest(t, e, i, s) {
        const n = ve(e, t);
        const o = i.axis || 'xy';
        const a = i.includeInvisible || !1;
        return Xi(t, n, o, i.intersect, s, a);
      },
      x: (t, e, i, s) => qi(t, ve(e, t), 'x', i.intersect, s),
      y: (t, e, i, s) => qi(t, ve(e, t), 'y', i.intersect, s),
    },
  };
  const Gi = ['left', 'top', 'right', 'bottom'];
  function Ji(t, e) {
    return t.filter(t => t.pos === e);
  }
  function Zi(t, e) {
    return t.filter(t => Gi.indexOf(t.pos) === -1 && t.box.axis === e);
  }
  function Qi(t, e) {
    return t.sort((t, i) => {
      const s = e ? i : t;
      const n = e ? t : i;
      return s.weight === n.weight ? s.index - n.index : s.weight - n.weight;
    });
  }
  function ts(t, e) {
    const i = (function(t) {
      const e = {};
      for (const i of t) {
        const { stack: t, pos: s, stackWeight: n } = i;
        if (!t || !Gi.includes(s)) {
          continue;
        }
        const o =
            e[t] || (e[t] = { count: 0, placed: 0, weight: 0, size: 0 });
        (o.count++, (o.weight += n));
      }
      return e;
    })(t);
    const { vBoxMaxWidth: s, hBoxMaxHeight: n } = e;
    let o, a, r;
    for (o = 0, a = t.length; o < a; ++o) {
      r = t[o];
      const { fullSize: a } = r.box;
      const l = i[r.stack];
      const h = l && r.stackWeight / l.weight;
      r.horizontal ?
        ((r.width = h ? h * s : a && e.availableWidth), (r.height = n)) :
        ((r.width = s), (r.height = h ? h * n : a && e.availableHeight));
    }
    return i;
  }
  function es(t, e, i, s) {
    return Math.max(t[i], e[i]) + Math.max(t[s], e[s]);
  }
  function is(t, e) {
    ((t.top = Math.max(t.top, e.top)),
    (t.left = Math.max(t.left, e.left)),
    (t.bottom = Math.max(t.bottom, e.bottom)),
    (t.right = Math.max(t.right, e.right)));
  }
  function ss(t, e, i, s) {
    const { pos: n, box: a } = i;
    const r = t.maxPadding;
    if (!o(n)) {
      i.size && (t[n] -= i.size);
      const e = s[i.stack] || { size: 0, count: 1 };
      ((e.size = Math.max(e.size, i.horizontal ? a.height : a.width)),
      (i.size = e.size / e.count),
      (t[n] += i.size));
    }
    a.getPadding && is(r, a.getPadding());
    const l = Math.max(0, e.outerWidth - es(r, t, 'left', 'right'));
    const h = Math.max(0, e.outerHeight - es(r, t, 'top', 'bottom'));
    const c = l !== t.w;
    const d = h !== t.h;
    return (
      (t.w = l),
      (t.h = h),
      i.horizontal ? { same: c, other: d } : { same: d, other: c }
    );
  }
  function ns(t, e) {
    const i = e.maxPadding;
    function s(t) {
      const s = { left: 0, top: 0, right: 0, bottom: 0 };
      return (
        t.forEach(t => {
          s[t] = Math.max(e[t], i[t]);
        }),
        s
      );
    }
    return s(t ? ['left', 'right'] : ['top', 'bottom']);
  }
  function os(t, e, i, s) {
    const n = [];
    let o, a, r, l, h, c;
    for (o = 0, a = t.length, h = 0; o < a; ++o) {
      ((r = t[o]),
      (l = r.box),
      l.update(r.width || e.w, r.height || e.h, ns(r.horizontal, e)));
      const { same: a, other: d } = ss(e, i, r, s);
      ((h |= a && n.length), (c = c || d), l.fullSize || n.push(r));
    }
    return (h && os(n, e, i, s)) || c;
  }
  function as(t, e, i, s, n) {
    ((t.top = i),
    (t.left = e),
    (t.right = e + s),
    (t.bottom = i + n),
    (t.width = s),
    (t.height = n));
  }
  function rs(t, e, i, s) {
    const n = i.padding;
    let { x: o, y: a } = e;
    for (const r of t) {
      const t = r.box;
      const l = s[r.stack] || { count: 1, placed: 0, weight: 1 };
      const h = r.stackWeight / l.weight || 1;
      if (r.horizontal) {
        const s = e.w * h;
        const o = l.size || t.height;
        (k(l.start) && (a = l.start),
        t.fullSize ?
          as(t, n.left, a, i.outerWidth - n.right - n.left, o) :
          as(t, e.left + l.placed, a, s, o),
        (l.start = a),
        (l.placed += s),
        (a = t.bottom));
      } else {
        const s = e.h * h;
        const a = l.size || t.width;
        (k(l.start) && (o = l.start),
        t.fullSize ?
          as(t, o, n.top, a, i.outerHeight - n.bottom - n.top) :
          as(t, o, e.top + l.placed, a, s),
        (l.start = o),
        (l.placed += s),
        (o = t.right));
      }
    }
    ((e.x = o), (e.y = a));
  }
  const ls = {
    addBox(t, e) {
      (t.boxes || (t.boxes = []),
      (e.fullSize = e.fullSize || !1),
      (e.position = e.position || 'top'),
      (e.weight = e.weight || 0),
      (e._layers =
          e._layers ||
          function() {
            return [
              {
                z: 0,
                draw(t) {
                  e.draw(t);
                },
              },
            ];
          }),
      t.boxes.push(e));
    },
    removeBox(t, e) {
      const i = t.boxes ? t.boxes.indexOf(e) : -1;
      i !== -1 && t.boxes.splice(i, 1);
    },
    configure(t, e, i) {
      ((e.fullSize = i.fullSize),
      (e.position = i.position),
      (e.weight = i.weight));
    },
    update(t, e, i, s) {
      if (!t) {
        return;
      }
      const n = ki(t.options.layout.padding);
      const o = Math.max(e - n.width, 0);
      const a = Math.max(i - n.height, 0);
      const r = (function(t) {
        const e = (function(t) {
          const e = [];
          let i, s, n, o, a, r;
          for (i = 0, s = (t || []).length; i < s; ++i) {
            ((n = t[i]),
            ({
              position: o,
              options: { stack: a, stackWeight: r = 1 },
            } = n),
            e.push({
              index: i,
              box: n,
              pos: o,
              horizontal: n.isHorizontal(),
              weight: n.weight,
              stack: a && o + a,
              stackWeight: r,
            }));
          }
          return e;
        })(t);
        const i = Qi(
          e.filter(t => t.box.fullSize),
          !0,
        );
        const s = Qi(Ji(e, 'left'), !0);
        const n = Qi(Ji(e, 'right'));
        const o = Qi(Ji(e, 'top'), !0);
        const a = Qi(Ji(e, 'bottom'));
        const r = Zi(e, 'x');
        const l = Zi(e, 'y');
        return {
          fullSize: i,
          leftAndTop: s.concat(o),
          rightAndBottom: n.concat(l).concat(a).concat(r),
          chartArea: Ji(e, 'chartArea'),
          vertical: s.concat(n).concat(l),
          horizontal: o.concat(a).concat(r),
        };
      })(t.boxes);
      const l = r.vertical;
      const h = r.horizontal;
      u(t.boxes, t => {
        typeof t.beforeLayout === 'function' && t.beforeLayout();
      });
      const c =
          l.reduce(
            (t, e) =>
              e.box.options && !1 === e.box.options.display ? t : t + 1,
            0,
          ) || 1;
      const d = Object.freeze({
        outerWidth: e,
        outerHeight: i,
        padding: n,
        availableWidth: o,
        availableHeight: a,
        vBoxMaxWidth: o / 2 / c,
        hBoxMaxHeight: a / 2,
      });
      const f = Object.assign({}, n);
      is(f, ki(s));
      const g = Object.assign(
        { maxPadding: f, w: o, h: a, x: n.left, y: n.top },
        n,
      );
      const p = ts(l.concat(h), d);
      (os(r.fullSize, g, d, p),
      os(l, g, d, p),
      os(h, g, d, p) && os(l, g, d, p),
      (function(t) {
        const e = t.maxPadding;
        function i(i) {
          const s = Math.max(e[i] - t[i], 0);
          return ((t[i] += s), s);
        }
        ((t.y += i('top')), (t.x += i('left')), i('right'), i('bottom'));
      })(g),
      rs(r.leftAndTop, g, d, p),
      (g.x += g.w),
      (g.y += g.h),
      rs(r.rightAndBottom, g, d, p),
      (t.chartArea = {
        left: g.left,
        top: g.top,
        right: g.left + g.w,
        bottom: g.top + g.h,
        height: g.h,
        width: g.w,
      }),
      u(r.chartArea, e => {
        const i = e.box;
        (Object.assign(i, t.chartArea),
        i.update(g.w, g.h, { left: 0, top: 0, right: 0, bottom: 0 }));
      }));
    },
  };
  class hs {
    acquireContext(t, e) {}
    releaseContext(t) {
      return !1;
    }

    addEventListener(t, e, i) {}
    removeEventListener(t, e, i) {}
    getDevicePixelRatio() {
      return 1;
    }

    getMaximumSize(t, e, i, s) {
      return (
        (e = Math.max(0, e || t.width)),
        (i = i || t.height),
        { width: e, height: Math.max(0, s ? Math.floor(e / s) : i) }
      );
    }

    isAttached(t) {
      return !0;
    }

    updateConfig(t) {}
  }
  class cs extends hs {
    acquireContext(t) {
      return (t && t.getContext && t.getContext('2d')) || null;
    }

    updateConfig(t) {
      t.options.animation = !1;
    }
  }
  const ds = '$chartjs';
  const us = {
    touchstart: 'mousedown',
    touchmove: 'mousemove',
    touchend: 'mouseup',
    pointerenter: 'mouseenter',
    pointerdown: 'mousedown',
    pointermove: 'mousemove',
    pointerup: 'mouseup',
    pointerleave: 'mouseout',
    pointerout: 'mouseout',
  };
  const fs = t => t === null || t === '';
  const gs = !!Se && { passive: !0 };
  function ps(t, e, i) {
    t && t.canvas && t.canvas.removeEventListener(e, i, gs);
  }
  function ms(t, e) {
    for (const i of t) {
      if (i === e || i.contains(e)) {
        return !0;
      }
    }
  }
  function xs(t, e, i) {
    const s = t.canvas;
    const n = new MutationObserver(t => {
      let e = !1;
      for (const i of t) {
        ((e = e || ms(i.addedNodes, s)), (e = e && !ms(i.removedNodes, s)));
      }
      e && i();
    });
    return (n.observe(document, { childList: !0, subtree: !0 }), n);
  }
  function bs(t, e, i) {
    const s = t.canvas;
    const n = new MutationObserver(t => {
      let e = !1;
      for (const i of t) {
        ((e = e || ms(i.removedNodes, s)), (e = e && !ms(i.addedNodes, s)));
      }
      e && i();
    });
    return (n.observe(document, { childList: !0, subtree: !0 }), n);
  }
  const _s = new Map();
  let ys = 0;
  function vs() {
    const t = window.devicePixelRatio;
    t !== ys &&
      ((ys = t),
      _s.forEach((e, i) => {
        i.currentDevicePixelRatio !== t && e();
      }));
  }
  function Ms(t, e, i) {
    const s = t.canvas;
    const n = s && ge(s);
    if (!n) {
      return;
    }
    const o = ct((t, e) => {
      const s = n.clientWidth;
      (i(t, e), s < n.clientWidth && i());
    }, window);
    const a = new ResizeObserver(t => {
      const e = t[0];
      const i = e.contentRect.width;
      const s = e.contentRect.height;
      (i === 0 && s === 0) || o(i, s);
    });
    return (
      a.observe(n),
      (function(t, e) {
        (_s.size || window.addEventListener('resize', vs), _s.set(t, e));
      })(t, o),
      a
    );
  }
  function ws(t, e, i) {
    (i && i.disconnect(),
    e === 'resize' &&
        (function(t) {
          (_s.delete(t), _s.size || window.removeEventListener('resize', vs));
        })(t));
  }
  function ks(t, e, i) {
    const s = t.canvas;
    const n = ct(e => {
      t.ctx !== null &&
          i(
            (function(t, e) {
              const i = us[t.type] || t.type;
              const { x: s, y: n } = ve(t, e);
              return {
                type: i,
                chart: e,
                native: t,
                x: void 0 !== s ? s : null,
                y: void 0 !== n ? n : null,
              };
            })(e, t),
          );
    }, t);
    return (
      (function(t, e, i) {
        t && t.addEventListener(e, i, gs);
      })(s, e, n),
      n
    );
  }
  class Ss extends hs {
    acquireContext(t, e) {
      const i = t && t.getContext && t.getContext('2d');
      return i && i.canvas === t ?
        ((function(t, e) {
          const i = t.style;
          const s = t.getAttribute('height');
          const n = t.getAttribute('width');
          if (
            ((t[ds] = {
              initial: {
                height: s,
                width: n,
                style: {
                  display: i.display,
                  height: i.height,
                  width: i.width,
                },
              },
            }),
            (i.display = i.display || 'block'),
            (i.boxSizing = i.boxSizing || 'border-box'),
            fs(n))
          ) {
            const e = Pe(t, 'width');
            void 0 !== e && (t.width = e);
          }
          if (fs(s)) {
            if (t.style.height === '') {
              t.height = t.width / (e || 2);
            } else {
              const e = Pe(t, 'height');
              void 0 !== e && (t.height = e);
            }
          }
        })(t, e),
        i) :
        null;
    }

    releaseContext(t) {
      const e = t.canvas;
      if (!e[ds]) {
        return !1;
      }
      const i = e[ds].initial;
      ['height', 'width'].forEach(t => {
        const n = i[t];
        s(n) ? e.removeAttribute(t) : e.setAttribute(t, n);
      });
      const n = i.style || {};
      return (
        Object.keys(n).forEach(t => {
          e.style[t] = n[t];
        }),
        (e.width = e.width),
        delete e[ds],
        !0
      );
    }

    addEventListener(t, e, i) {
      this.removeEventListener(t, e);
      const s = t.$proxies || (t.$proxies = {});
      const n = { attach: xs, detach: bs, resize: Ms }[e] || ks;
      s[e] = n(t, e, i);
    }

    removeEventListener(t, e) {
      const i = t.$proxies || (t.$proxies = {});
      const s = i[e];
      if (!s) {
        return;
      }
      ((({ attach: ws, detach: ws, resize: ws })[e] || ps)(t, e, s),
      (i[e] = void 0));
    }

    getDevicePixelRatio() {
      return window.devicePixelRatio;
    }

    getMaximumSize(t, e, i, s) {
      return we(t, e, i, s);
    }

    isAttached(t) {
      const e = t && ge(t);
      return !(!e || !e.isConnected);
    }
  }
  function Ps(t) {
    return !fe() ||
      (typeof OffscreenCanvas !== 'undefined' && t instanceof OffscreenCanvas) ?
      cs :
      Ss;
  }
  const Ds = Object.freeze({
    __proto__: null,
    BasePlatform: hs,
    BasicPlatform: cs,
    DomPlatform: Ss,
    _detectPlatform: Ps,
  });
  const Cs = 'transparent';
  const Os = {
    boolean: (t, e, i) => (i > 0.5 ? e : t),
    color(t, e, i) {
      const s = Qt(t || Cs);
      const n = s.valid && Qt(e || Cs);
      return n && n.valid ? n.mix(s, i).hexString() : e;
    },
    number: (t, e, i) => t + (e - t) * i,
  };
  class As {
    constructor(t, e, i, s) {
      const n = e[i];
      s = Pi([t.to, s, n, t.from]);
      const o = Pi([t.from, n, s]);
      ((this._active = !0),
      (this._fn = t.fn || Os[t.type || typeof o]),
      (this._easing = fi[t.easing] || fi.linear),
      (this._start = Math.floor(Date.now() + (t.delay || 0))),
      (this._duration = this._total = Math.floor(t.duration)),
      (this._loop = !!t.loop),
      (this._target = e),
      (this._prop = i),
      (this._from = o),
      (this._to = s),
      (this._promises = void 0));
    }

    active() {
      return this._active;
    }

    update(t, e, i) {
      if (this._active) {
        this._notify(!1);
        const s = this._target[this._prop];
        const n = i - this._start;
        const o = this._duration - n;
        ((this._start = i),
        (this._duration = Math.floor(Math.max(o, t.duration))),
        (this._total += n),
        (this._loop = !!t.loop),
        (this._to = Pi([t.to, e, s, t.from])),
        (this._from = Pi([t.from, s, e])));
      }
    }

    cancel() {
      this._active &&
        (this.tick(Date.now()), (this._active = !1), this._notify(!1));
    }

    tick(t) {
      const e = t - this._start;
      const i = this._duration;
      const s = this._prop;
      const n = this._from;
      const o = this._loop;
      const a = this._to;
      let r;
      if (((this._active = n !== a && (o || e < i)), !this._active)) {
        return ((this._target[s] = a), void this._notify(!0));
      }
      e < 0 ?
        (this._target[s] = n) :
        ((r = (e / i) % 2),
        (r = o && r > 1 ? 2 - r : r),
        (r = this._easing(Math.min(1, Math.max(0, r)))),
        (this._target[s] = this._fn(n, a, r)));
    }

    wait() {
      const t = this._promises || (this._promises = []);
      return new Promise((e, i) => {
        t.push({ res: e, rej: i });
      });
    }

    _notify(t) {
      const e = t ? 'res' : 'rej';
      const i = this._promises || [];
      for (let t = 0; t < i.length; t++) {
        i[t][e]();
      }
    }
  }
  class Ts {
    constructor(t, e) {
      ((this._chart = t), (this._properties = new Map()), this.configure(e));
    }

    configure(t) {
      if (!o(t)) {
        return;
      }
      const e = Object.keys(ue.animation);
      const i = this._properties;
      Object.getOwnPropertyNames(t).forEach(s => {
        const a = t[s];
        if (!o(a)) {
          return;
        }
        const r = {};
        for (const t of e) {
          r[t] = a[t];
        }
        ((n(a.properties) && a.properties) || [s]).forEach(t => {
          (t !== s && i.has(t)) || i.set(t, r);
        });
      });
    }

    _animateOptions(t, e) {
      const i = e.options;
      const s = (function(t, e) {
        if (!e) {
          return;
        }
        let i = t.options;
        if (!i) {
          return void (t.options = e);
        }
        i.$shared &&
            (t.options = i =
              Object.assign({}, i, { $shared: !1, $animations: {} }));
        return i;
      })(t, i);
      if (!s) {
        return [];
      }
      const n = this._createAnimations(s, i);
      return (
        i.$shared &&
          (function(t, e) {
            const i = [];
            const s = Object.keys(e);
            for (let e = 0; e < s.length; e++) {
              const n = t[s[e]];
              n && n.active() && i.push(n.wait());
            }
            return Promise.all(i);
          })(t.options.$animations, i).then(
            () => {
              t.options = i;
            },
            () => {},
          ),
        n
      );
    }

    _createAnimations(t, e) {
      const i = this._properties;
      const s = [];
      const n = t.$animations || (t.$animations = {});
      const o = Object.keys(e);
      const a = Date.now();
      let r;
      for (r = o.length - 1; r >= 0; --r) {
        const l = o[r];
        if (l.charAt(0) === '$') {
          continue;
        }
        if (l === 'options') {
          s.push(...this._animateOptions(t, e));
          continue;
        }
        const h = e[l];
        let c = n[l];
        const d = i.get(l);
        if (c) {
          if (d && c.active()) {
            c.update(d, h, a);
            continue;
          }
          c.cancel();
        }
        d && d.duration ?
          ((n[l] = c = new As(d, t, l, h)), s.push(c)) :
          (t[l] = h);
      }
      return s;
    }

    update(t, e) {
      if (this._properties.size === 0) {
        return void Object.assign(t, e);
      }
      const i = this._createAnimations(t, e);
      return i.length ? (bt.add(this._chart, i), !0) : void 0;
    }
  }
  function Ls(t, e) {
    const i = (t && t.options) || {};
    const s = i.reverse;
    const n = void 0 === i.min ? e : 0;
    const o = void 0 === i.max ? e : 0;
    return { start: s ? o : n, end: s ? n : o };
  }
  function Es(t, e) {
    const i = [];
    const s = t._getSortedDatasetMetas(e);
    let n, o;
    for (n = 0, o = s.length; n < o; ++n) {
      i.push(s[n].index);
    }
    return i;
  }
  function Rs(t, e, i, s = {}) {
    const n = t.keys;
    const o = s.mode === 'single';
    let r, l, h, c;
    if (e === null) {
      return;
    }
    let d = !1;
    for (r = 0, l = n.length; r < l; ++r) {
      if (((h = +n[r]), h === i)) {
        if (((d = !0), s.all)) {
          continue;
        }
        break;
      }
      ((c = t.values[h]), a(c) && (o || e === 0 || F(e) === F(c)) && (e += c));
    }
    return d || s.all ? e : 0;
  }
  function Is(t, e) {
    const i = t && t.options.stacked;
    return i || (void 0 === i && void 0 !== e.stack);
  }
  function zs(t, e, i) {
    const s = t[e] || (t[e] = {});
    return s[i] || (s[i] = {});
  }
  function Fs(t, e, i, s) {
    for (const n of e.getMatchingVisibleMetas(s).reverse()) {
      const e = t[n.index];
      if ((i && e > 0) || (!i && e < 0)) {
        return n.index;
      }
    }
    return null;
  }
  function Vs(t, e) {
    const { chart: i, _cachedMeta: s } = t;
    const n = i._stacks || (i._stacks = {});
    const { iScale: o, vScale: a, index: r } = s;
    const l = o.axis;
    const h = a.axis;
    const c = (function(t, e, i) {
      return `${t.id}.${e.id}.${i.stack || i.type}`;
    })(o, a, s);
    const d = e.length;
    let u;
    for (let t = 0; t < d; ++t) {
      const i = e[t];
      const { [l]: o, [h]: d } = i;
      ((u = (i._stacks || (i._stacks = {}))[h] = zs(n, c, o)),
      (u[r] = d),
      (u._top = Fs(u, a, !0, s.type)),
      (u._bottom = Fs(u, a, !1, s.type)));
      (u._visualValues || (u._visualValues = {}))[r] = d;
    }
  }
  function Bs(t, e) {
    const i = t.scales;
    return Object.keys(i)
      .filter(t => i[t].axis === e)
      .shift();
  }
  function Ws(t, e) {
    const i = t.controller.index;
    const s = t.vScale && t.vScale.axis;
    if (s) {
      e = e || t._parsed;
      for (const t of e) {
        const e = t._stacks;
        if (!e || void 0 === e[s] || void 0 === e[s][i]) {
          return;
        }
        (delete e[s][i],
        void 0 !== e[s]._visualValues &&
            void 0 !== e[s]._visualValues[i] &&
            delete e[s]._visualValues[i]);
      }
    }
  }
  const Ns = t => t === 'reset' || t === 'none';
  const Hs = (t, e) => (e ? t : Object.assign({}, t));
  class js {
    static defaults = {};
    static datasetElementType = null;
    static dataElementType = null;
    constructor(t, e) {
      ((this.chart = t),
      (this._ctx = t.ctx),
      (this.index = e),
      (this._cachedDataOpts = {}),
      (this._cachedMeta = this.getMeta()),
      (this._type = this._cachedMeta.type),
      (this.options = void 0),
      (this._parsing = !1),
      (this._data = void 0),
      (this._objectData = void 0),
      (this._sharedOptions = void 0),
      (this._drawStart = void 0),
      (this._drawCount = void 0),
      (this.enableOptionSharing = !1),
      (this.supportsDecimation = !1),
      (this.$context = void 0),
      (this._syncList = []),
      (this.datasetElementType = new.target.datasetElementType),
      (this.dataElementType = new.target.dataElementType),
      this.initialize());
    }

    initialize() {
      const t = this._cachedMeta;
      (this.configure(),
      this.linkScales(),
      (t._stacked = Is(t.vScale, t)),
      this.addElements(),
      this.options.fill &&
          !this.chart.isPluginEnabled('filler') &&
          console.warn(
            'Tried to use the \'fill\' option without the \'Filler\' plugin enabled. Please import and register the \'Filler\' plugin and make sure it is not disabled in the options',
          ));
    }

    updateIndex(t) {
      (this.index !== t && Ws(this._cachedMeta), (this.index = t));
    }

    linkScales() {
      const t = this.chart;
      const e = this._cachedMeta;
      const i = this.getDataset();
      const s = (t, e, i, s) => (t === 'x' ? e : t === 'r' ? s : i);
      const n = (e.xAxisID = l(i.xAxisID, Bs(t, 'x')));
      const o = (e.yAxisID = l(i.yAxisID, Bs(t, 'y')));
      const a = (e.rAxisID = l(i.rAxisID, Bs(t, 'r')));
      const r = e.indexAxis;
      const h = (e.iAxisID = s(r, n, o, a));
      const c = (e.vAxisID = s(r, o, n, a));
      ((e.xScale = this.getScaleForId(n)),
      (e.yScale = this.getScaleForId(o)),
      (e.rScale = this.getScaleForId(a)),
      (e.iScale = this.getScaleForId(h)),
      (e.vScale = this.getScaleForId(c)));
    }

    getDataset() {
      return this.chart.data.datasets[this.index];
    }

    getMeta() {
      return this.chart.getDatasetMeta(this.index);
    }

    getScaleForId(t) {
      return this.chart.scales[t];
    }

    _getOtherScale(t) {
      const e = this._cachedMeta;
      return t === e.iScale ? e.vScale : e.iScale;
    }

    reset() {
      this._update('reset');
    }

    _destroy() {
      const t = this._cachedMeta;
      (this._data && rt(this._data, this), t._stacked && Ws(t));
    }

    _dataCheck() {
      const t = this.getDataset();
      const e = t.data || (t.data = []);
      const i = this._data;
      if (o(e)) {
        const t = this._cachedMeta;
        this._data = (function(t, e) {
          const { iScale: i, vScale: s } = e;
          const n = i.axis === 'x' ? 'x' : 'y';
          const o = s.axis === 'x' ? 'x' : 'y';
          const a = Object.keys(t);
          const r = new Array(a.length);
          let l, h, c;
          for (l = 0, h = a.length; l < h; ++l) {
            ((c = a[l]), (r[l] = { [n]: c, [o]: t[c] }));
          }
          return r;
        })(e, t);
      } else if (i !== e) {
        if (i) {
          rt(i, this);
          const t = this._cachedMeta;
          (Ws(t), (t._parsed = []));
        }
        (e && Object.isExtensible(e) && at(e, this),
        (this._syncList = []),
        (this._data = e));
      }
    }

    addElements() {
      const t = this._cachedMeta;
      (this._dataCheck(),
      this.datasetElementType && (t.dataset = new this.datasetElementType()));
    }

    buildOrUpdateElements(t) {
      const e = this._cachedMeta;
      const i = this.getDataset();
      let s = !1;
      this._dataCheck();
      const n = e._stacked;
      ((e._stacked = Is(e.vScale, e)),
      e.stack !== i.stack && ((s = !0), Ws(e), (e.stack = i.stack)),
      this._resyncElements(t),
      (s || n !== e._stacked) &&
          (Vs(this, e._parsed), (e._stacked = Is(e.vScale, e))));
    }

    configure() {
      const t = this.chart.config;
      const e = t.datasetScopeKeys(this._type);
      const i = t.getOptionScopes(this.getDataset(), e, !0);
      ((this.options = t.createResolver(i, this.getContext())),
      (this._parsing = this.options.parsing),
      (this._cachedDataOpts = {}));
    }

    parse(t, e) {
      const { _cachedMeta: i, _data: s } = this;
      const { iScale: a, _stacked: r } = i;
      const l = a.axis;
      let h;
      let c;
      let d;
      let u = (t === 0 && e === s.length) || i._sorted;
      let f = t > 0 && i._parsed[t - 1];
      if (!1 === this._parsing) {
        ((i._parsed = s), (i._sorted = !0), (d = s));
      } else {
        d = n(s[t]) ?
          this.parseArrayData(i, s, t, e) :
          o(s[t]) ?
            this.parseObjectData(i, s, t, e) :
            this.parsePrimitiveData(i, s, t, e);
        const a = () => c[l] === null || (f && c[l] < f[l]);
        for (h = 0; h < e; ++h) {
          ((i._parsed[h + t] = c = d[h]), u && (a() && (u = !1), (f = c)));
        }
        i._sorted = u;
      }
      r && Vs(this, d);
    }

    parsePrimitiveData(t, e, i, s) {
      const { iScale: n, vScale: o } = t;
      const a = n.axis;
      const r = o.axis;
      const l = n.getLabels();
      const h = n === o;
      const c = new Array(s);
      let d, u, f;
      for (d = 0, u = s; d < u; ++d) {
        ((f = d + i),
        (c[d] = { [a]: h || n.parse(l[f], f), [r]: o.parse(e[f], f) }));
      }
      return c;
    }

    parseArrayData(t, e, i, s) {
      const { xScale: n, yScale: o } = t;
      const a = new Array(s);
      let r, l, h, c;
      for (r = 0, l = s; r < l; ++r) {
        ((h = r + i),
        (c = e[h]),
        (a[r] = { x: n.parse(c[0], h), y: o.parse(c[1], h) }));
      }
      return a;
    }

    parseObjectData(t, e, i, s) {
      const { xScale: n, yScale: o } = t;
      const { xAxisKey: a = 'x', yAxisKey: r = 'y' } = this._parsing;
      const l = new Array(s);
      let h, c, d, u;
      for (h = 0, c = s; h < c; ++h) {
        ((d = h + i),
        (u = e[d]),
        (l[h] = { x: n.parse(M(u, a), d), y: o.parse(M(u, r), d) }));
      }
      return l;
    }

    getParsed(t) {
      return this._cachedMeta._parsed[t];
    }

    getDataElement(t) {
      return this._cachedMeta.data[t];
    }

    applyStack(t, e, i) {
      const s = this.chart;
      const n = this._cachedMeta;
      const o = e[t.axis];
      return Rs(
        { keys: Es(s, !0), values: e._stacks[t.axis]._visualValues },
        o,
        n.index,
        { mode: i },
      );
    }

    updateRangeFromParsed(t, e, i, s) {
      const n = i[e.axis];
      let o = n === null ? NaN : n;
      const a = s && i._stacks[e.axis];
      (s && a && ((s.values = a), (o = Rs(s, n, this._cachedMeta.index))),
      (t.min = Math.min(t.min, o)),
      (t.max = Math.max(t.max, o)));
    }

    getMinMax(t, e) {
      const i = this._cachedMeta;
      const s = i._parsed;
      const n = i._sorted && t === i.iScale;
      const o = s.length;
      const r = this._getOtherScale(t);
      const l = ((t, e, i) =>
        t && !e.hidden && e._stacked && { keys: Es(i, !0), values: null })(
        e,
        i,
        this.chart,
      );
      const h = { min: Number.POSITIVE_INFINITY, max: Number.NEGATIVE_INFINITY };
      const { min: c, max: d } = (function(t) {
        const {
          min: e,
          max: i,
          minDefined: s,
          maxDefined: n,
        } = t.getUserBounds();
        return {
          min: s ? e : Number.NEGATIVE_INFINITY,
          max: n ? i : Number.POSITIVE_INFINITY,
        };
      })(r);
      let u, f;
      function g() {
        f = s[u];
        const e = f[r.axis];
        return !a(f[t.axis]) || c > e || d < e;
      }
      for (
        u = 0;
        u < o && (g() || (this.updateRangeFromParsed(h, t, f, l), !n));
        ++u
      ) {
        ;
      }
      if (n) {
        for (u = o - 1; u >= 0; --u) {
          if (!g()) {
            this.updateRangeFromParsed(h, t, f, l);
            break;
          }
        }
      }
      return h;
    }

    getAllParsedValues(t) {
      const e = this._cachedMeta._parsed;
      const i = [];
      let s, n, o;
      for (s = 0, n = e.length; s < n; ++s) {
        ((o = e[s][t.axis]), a(o) && i.push(o));
      }
      return i;
    }

    getMaxOverflow() {
      return !1;
    }

    getLabelAndValue(t) {
      const e = this._cachedMeta;
      const i = e.iScale;
      const s = e.vScale;
      const n = this.getParsed(t);
      return {
        label: i ? `${i.getLabelForValue(n[i.axis])}` : '',
        value: s ? `${s.getLabelForValue(n[s.axis])}` : '',
      };
    }

    _update(t) {
      const e = this._cachedMeta;
      (this.update(t || 'default'),
      (e._clip = (function(t) {
        let e, i, s, n;
        return (
          o(t) ?
            ((e = t.top), (i = t.right), (s = t.bottom), (n = t.left)) :
            (e = i = s = n = t),
          { top: e, right: i, bottom: s, left: n, disabled: !1 === t }
        );
      })(
        l(
          this.options.clip,
          (function(t, e, i) {
            if (!1 === i) {
              return !1;
            }
            const s = Ls(t, i);
            const n = Ls(e, i);
            return {
              top: n.end,
              right: s.end,
              bottom: n.start,
              left: s.start,
            };
          })(e.xScale, e.yScale, this.getMaxOverflow()),
        ),
      )));
    }

    update(t) {}
    draw() {
      const t = this._ctx;
      const e = this.chart;
      const i = this._cachedMeta;
      const s = i.data || [];
      const n = e.chartArea;
      const o = [];
      const a = this._drawStart || 0;
      const r = this._drawCount || s.length - a;
      const l = this.options.drawActiveElementsOnTop;
      let h;
      for (i.dataset && i.dataset.draw(t, n, a, r), h = a; h < a + r; ++h) {
        const e = s[h];
        e.hidden || (e.active && l ? o.push(e) : e.draw(t, n));
      }
      for (h = 0; h < o.length; ++h) {
        o[h].draw(t, n);
      }
    }

    getStyle(t, e) {
      const i = e ? 'active' : 'default';
      return void 0 === t && this._cachedMeta.dataset ?
        this.resolveDatasetElementOptions(i) :
        this.resolveDataElementOptions(t || 0, i);
    }

    getContext(t, e, i) {
      const s = this.getDataset();
      let n;
      if (t >= 0 && t < this._cachedMeta.data.length) {
        const e = this._cachedMeta.data[t];
        ((n =
          e.$context ||
          (e.$context = (function(t, e, i) {
            return Ci(t, {
              active: !1,
              dataIndex: e,
              parsed: void 0,
              raw: void 0,
              element: i,
              index: e,
              mode: 'default',
              type: 'data',
            });
          })(this.getContext(), t, e))),
        (n.parsed = this.getParsed(t)),
        (n.raw = s.data[t]),
        (n.index = n.dataIndex = t));
      } else {
        ((n =
          this.$context ||
          (this.$context = (function(t, e) {
            return Ci(t, {
              active: !1,
              dataset: void 0,
              datasetIndex: e,
              index: e,
              mode: 'default',
              type: 'dataset',
            });
          })(this.chart.getContext(), this.index))),
        (n.dataset = s),
        (n.index = n.datasetIndex = this.index));
      }
      return ((n.active = !!e), (n.mode = i), n);
    }

    resolveDatasetElementOptions(t) {
      return this._resolveElementOptions(this.datasetElementType.id, t);
    }

    resolveDataElementOptions(t, e) {
      return this._resolveElementOptions(this.dataElementType.id, e, t);
    }

    _resolveElementOptions(t, e = 'default', i) {
      const s = e === 'active';
      const n = this._cachedDataOpts;
      const o = `${t}-${e}`;
      const a = n[o];
      const r = this.enableOptionSharing && k(i);
      if (a) {
        return Hs(a, r);
      }
      const l = this.chart.config;
      const h = l.datasetElementScopeKeys(this._type, t);
      const c = s ? [`${t}Hover`, 'hover', t, ''] : [t, ''];
      const d = l.getOptionScopes(this.getDataset(), h);
      const u = Object.keys(ue.elements[t]);
      const f = l.resolveNamedOptions(d, u, () => this.getContext(i, s, e), c);
      return (
        f.$shared && ((f.$shared = r), (n[o] = Object.freeze(Hs(f, r)))),
        f
      );
    }

    _resolveAnimations(t, e, i) {
      const s = this.chart;
      const n = this._cachedDataOpts;
      const o = `animation-${e}`;
      const a = n[o];
      if (a) {
        return a;
      }
      let r;
      if (!1 !== s.options.animation) {
        const s = this.chart.config;
        const n = s.datasetAnimationScopeKeys(this._type, e);
        const o = s.getOptionScopes(this.getDataset(), n);
        r = s.createResolver(o, this.getContext(t, i, e));
      }
      const l = new Ts(s, r && r.animations);
      return (r && r._cacheable && (n[o] = Object.freeze(l)), l);
    }

    getSharedOptions(t) {
      if (t.$shared) {
        return (
          this._sharedOptions || (this._sharedOptions = Object.assign({}, t))
        );
      }
    }

    includeOptions(t, e) {
      return !e || Ns(t) || this.chart._animationsDisabled;
    }

    _getSharedOptions(t, e) {
      const i = this.resolveDataElementOptions(t, e);
      const s = this._sharedOptions;
      const n = this.getSharedOptions(i);
      const o = this.includeOptions(e, n) || n !== s;
      return (
        this.updateSharedOptions(n, e, i),
        { sharedOptions: n, includeOptions: o }
      );
    }

    updateElement(t, e, i, s) {
      Ns(s) ? Object.assign(t, i) : this._resolveAnimations(e, s).update(t, i);
    }

    updateSharedOptions(t, e, i) {
      t && !Ns(e) && this._resolveAnimations(void 0, e).update(t, i);
    }

    _setStyle(t, e, i, s) {
      t.active = s;
      const n = this.getStyle(e, s);
      this._resolveAnimations(e, i, s).update(t, {
        options: (!s && this.getSharedOptions(n)) || n,
      });
    }

    removeHoverStyle(t, e, i) {
      this._setStyle(t, i, 'active', !1);
    }

    setHoverStyle(t, e, i) {
      this._setStyle(t, i, 'active', !0);
    }

    _removeDatasetHoverStyle() {
      const t = this._cachedMeta.dataset;
      t && this._setStyle(t, void 0, 'active', !1);
    }

    _setDatasetHoverStyle() {
      const t = this._cachedMeta.dataset;
      t && this._setStyle(t, void 0, 'active', !0);
    }

    _resyncElements(t) {
      const e = this._data;
      const i = this._cachedMeta.data;
      for (const [t, e, i] of this._syncList) {
        this[t](e, i);
      }
      this._syncList = [];
      const s = i.length;
      const n = e.length;
      const o = Math.min(n, s);
      (o && this.parse(0, o),
      n > s ?
        this._insertElements(s, n - s, t) :
        n < s && this._removeElements(n, s - n));
    }

    _insertElements(t, e, i = !0) {
      const s = this._cachedMeta;
      const n = s.data;
      const o = t + e;
      let a;
      const r = t => {
        for (t.length += e, a = t.length - 1; a >= o; a--) {
          t[a] = t[a - e];
        }
      };
      for (r(n), a = t; a < o; ++a) {
        n[a] = new this.dataElementType();
      }
      (this._parsing && r(s._parsed),
      this.parse(t, e),
      i && this.updateElements(n, t, e, 'reset'));
    }

    updateElements(t, e, i, s) {}
    _removeElements(t, e) {
      const i = this._cachedMeta;
      if (this._parsing) {
        const s = i._parsed.splice(t, e);
        i._stacked && Ws(i, s);
      }
      i.data.splice(t, e);
    }

    _sync(t) {
      if (this._parsing) {
        this._syncList.push(t);
      } else {
        const [e, i, s] = t;
        this[e](i, s);
      }
      this.chart._dataChanges.push([this.index, ...t]);
    }

    _onDataPush() {
      const t = arguments.length;
      this._sync(['_insertElements', this.getDataset().data.length - t, t]);
    }

    _onDataPop() {
      this._sync(['_removeElements', this._cachedMeta.data.length - 1, 1]);
    }

    _onDataShift() {
      this._sync(['_removeElements', 0, 1]);
    }

    _onDataSplice(t, e) {
      e && this._sync(['_removeElements', t, e]);
      const i = arguments.length - 2;
      i && this._sync(['_insertElements', t, i]);
    }

    _onDataUnshift() {
      this._sync(['_insertElements', 0, arguments.length]);
    }
  }
  class $s {
    static defaults = {};
    static defaultRoutes = void 0;
    x;
    y;
    active = !1;
    options;
    $animations;
    tooltipPosition(t) {
      const { x: e, y: i } = this.getProps(['x', 'y'], t);
      return { x: e, y: i };
    }

    hasValue() {
      return N(this.x) && N(this.y);
    }

    getProps(t, e) {
      const i = this.$animations;
      if (!e || !i) {
        return this;
      }
      const s = {};
      return (
        t.forEach(t => {
          s[t] = i[t] && i[t].active() ? i[t]._to : this[t];
        }),
        s
      );
    }
  }
  function Ys(t, e) {
    const i = t.options.ticks;
    const n = (function(t) {
      const e = t.options.offset;
      const i = t._tickSize();
      const s = t._length / i + (e ? 0 : 1);
      const n = t._maxLength / i;
      return Math.floor(Math.min(s, n));
    })(t);
    const o = Math.min(i.maxTicksLimit || n, n);
    const a = i.major.enabled ?
      (function(t) {
        const e = [];
        let i, s;
        for (i = 0, s = t.length; i < s; i++) {
          t[i].major && e.push(i);
        }
        return e;
      })(e) :
      [];
    const r = a.length;
    const l = a[0];
    const h = a[r - 1];
    const c = [];
    if (r > o) {
      return (
        (function(t, e, i, s) {
          let n;
          let o = 0;
          let a = i[0];
          for (s = Math.ceil(s), n = 0; n < t.length; n++) {
            n === a && (e.push(t[n]), o++, (a = i[o * s]));
          }
        })(e, c, a, r / o),
        c
      );
    }
    const d = (function(t, e, i) {
      const s = (function(t) {
        const e = t.length;
        let i, s;
        if (e < 2) {
          return !1;
        }
        for (s = t[0], i = 1; i < e; ++i) {
          if (t[i] - t[i - 1] !== s) {
            return !1;
          }
        }
        return s;
      })(t);
      const n = e.length / i;
      if (!s) {
        return Math.max(n, 1);
      }
      const o = W(s);
      for (let t = 0, e = o.length - 1; t < e; t++) {
        const e = o[t];
        if (e > n) {
          return e;
        }
      }
      return Math.max(n, 1);
    })(a, e, o);
    if (r > 0) {
      let t, i;
      const n = r > 1 ? Math.round((h - l) / (r - 1)) : null;
      for (Us(e, c, d, s(n) ? 0 : l - n, l), t = 0, i = r - 1; t < i; t++) {
        Us(e, c, d, a[t], a[t + 1]);
      }
      return (Us(e, c, d, h, s(n) ? e.length : h + n), c);
    }
    return (Us(e, c, d), c);
  }
  function Us(t, e, i, s, n) {
    const o = l(s, 0);
    const a = Math.min(l(n, t.length), t.length);
    let r;
    let h;
    let c;
    let d = 0;
    for (
      i = Math.ceil(i), n && ((r = n - s), (i = r / Math.floor(r / i))), c = o;
      c < 0;

    ) {
      (d++, (c = Math.round(o + d * i)));
    }
    for (h = Math.max(o, 0); h < a; h++) {
      h === c && (e.push(t[h]), d++, (c = Math.round(o + d * i)));
    }
  }
  const Xs = (t, e, i) => (e === 'top' || e === 'left' ? t[e] + i : t[e] - i);
  const qs = (t, e) => Math.min(e || t, t);
  function Ks(t, e) {
    const i = [];
    const s = t.length / e;
    const n = t.length;
    let o = 0;
    for (; o < n; o += s) {
      i.push(t[Math.floor(o)]);
    }
    return i;
  }
  function Gs(t, e, i) {
    const s = t.ticks.length;
    const n = Math.min(e, s - 1);
    const o = t._startPixel;
    const a = t._endPixel;
    const r = 1e-6;
    let l;
    let h = t.getPixelForTick(n);
    if (
      !(
        i &&
        ((l =
          s === 1 ?
            Math.max(h - o, a - h) :
            e === 0 ?
              (t.getPixelForTick(1) - h) / 2 :
              (h - t.getPixelForTick(n - 1)) / 2),
        (h += n < e ? l : -l),
        h < o - r || h > a + r)
      )
    ) {
      return h;
    }
  }
  function Js(t) {
    return t.drawTicks ? t.tickLength : 0;
  }
  function Zs(t, e) {
    if (!t.display) {
      return 0;
    }
    const i = Si(t.font, e);
    const s = ki(t.padding);
    return (n(t.text) ? t.text.length : 1) * i.lineHeight + s.height;
  }
  function Qs(t, e, i) {
    let s = ut(t);
    return (
      ((i && e !== 'right') || (!i && e === 'right')) &&
        (s = (t => (t === 'left' ? 'right' : t === 'right' ? 'left' : t))(s)),
      s
    );
  }
  class tn extends $s {
    constructor(t) {
      (super(),
      (this.id = t.id),
      (this.type = t.type),
      (this.options = void 0),
      (this.ctx = t.ctx),
      (this.chart = t.chart),
      (this.top = void 0),
      (this.bottom = void 0),
      (this.left = void 0),
      (this.right = void 0),
      (this.width = void 0),
      (this.height = void 0),
      (this._margins = { left: 0, right: 0, top: 0, bottom: 0 }),
      (this.maxWidth = void 0),
      (this.maxHeight = void 0),
      (this.paddingTop = void 0),
      (this.paddingBottom = void 0),
      (this.paddingLeft = void 0),
      (this.paddingRight = void 0),
      (this.axis = void 0),
      (this.labelRotation = void 0),
      (this.min = void 0),
      (this.max = void 0),
      (this._range = void 0),
      (this.ticks = []),
      (this._gridLineItems = null),
      (this._labelItems = null),
      (this._labelSizes = null),
      (this._length = 0),
      (this._maxLength = 0),
      (this._longestTextCache = {}),
      (this._startPixel = void 0),
      (this._endPixel = void 0),
      (this._reversePixels = !1),
      (this._userMax = void 0),
      (this._userMin = void 0),
      (this._suggestedMax = void 0),
      (this._suggestedMin = void 0),
      (this._ticksLength = 0),
      (this._borderValue = 0),
      (this._cache = {}),
      (this._dataLimitsCached = !1),
      (this.$context = void 0));
    }

    init(t) {
      ((this.options = t.setContext(this.getContext())),
      (this.axis = t.axis),
      (this._userMin = this.parse(t.min)),
      (this._userMax = this.parse(t.max)),
      (this._suggestedMin = this.parse(t.suggestedMin)),
      (this._suggestedMax = this.parse(t.suggestedMax)));
    }

    parse(t, e) {
      return t;
    }

    getUserBounds() {
      let {
        _userMin: t,
        _userMax: e,
        _suggestedMin: i,
        _suggestedMax: s,
      } = this;
      return (
        (t = r(t, Number.POSITIVE_INFINITY)),
        (e = r(e, Number.NEGATIVE_INFINITY)),
        (i = r(i, Number.POSITIVE_INFINITY)),
        (s = r(s, Number.NEGATIVE_INFINITY)),
        { min: r(t, i), max: r(e, s), minDefined: a(t), maxDefined: a(e) }
      );
    }

    getMinMax(t) {
      let e;
      let { min: i, max: s, minDefined: n, maxDefined: o } = this.getUserBounds();
      if (n && o) {
        return { min: i, max: s };
      }
      const a = this.getMatchingVisibleMetas();
      for (let r = 0, l = a.length; r < l; ++r) {
        ((e = a[r].controller.getMinMax(this, t)),
        n || (i = Math.min(i, e.min)),
        o || (s = Math.max(s, e.max)));
      }
      return (
        (i = o && i > s ? s : i),
        (s = n && i > s ? i : s),
        { min: r(i, r(s, i)), max: r(s, r(i, s)) }
      );
    }

    getPadding() {
      return {
        left: this.paddingLeft || 0,
        top: this.paddingTop || 0,
        right: this.paddingRight || 0,
        bottom: this.paddingBottom || 0,
      };
    }

    getTicks() {
      return this.ticks;
    }

    getLabels() {
      const t = this.chart.data;
      return (
        this.options.labels ||
        (this.isHorizontal() ? t.xLabels : t.yLabels) ||
        t.labels ||
        []
      );
    }

    getLabelItems(t = this.chart.chartArea) {
      return (
        this._labelItems || (this._labelItems = this._computeLabelItems(t))
      );
    }

    beforeLayout() {
      ((this._cache = {}), (this._dataLimitsCached = !1));
    }

    beforeUpdate() {
      d(this.options.beforeUpdate, [this]);
    }

    update(t, e, i) {
      const { beginAtZero: s, grace: n, ticks: o } = this.options;
      const a = o.sampleSize;
      (this.beforeUpdate(),
      (this.maxWidth = t),
      (this.maxHeight = e),
      (this._margins = i =
          Object.assign({ left: 0, right: 0, top: 0, bottom: 0 }, i)),
      (this.ticks = null),
      (this._labelSizes = null),
      (this._gridLineItems = null),
      (this._labelItems = null),
      this.beforeSetDimensions(),
      this.setDimensions(),
      this.afterSetDimensions(),
      (this._maxLength = this.isHorizontal() ?
        this.width + i.left + i.right :
        this.height + i.top + i.bottom),
      this._dataLimitsCached ||
          (this.beforeDataLimits(),
          this.determineDataLimits(),
          this.afterDataLimits(),
          (this._range = Di(this, n, s)),
          (this._dataLimitsCached = !0)),
      this.beforeBuildTicks(),
      (this.ticks = this.buildTicks() || []),
      this.afterBuildTicks());
      const r = a < this.ticks.length;
      (this._convertTicksToLabels(r ? Ks(this.ticks, a) : this.ticks),
      this.configure(),
      this.beforeCalculateLabelRotation(),
      this.calculateLabelRotation(),
      this.afterCalculateLabelRotation(),
      o.display &&
          (o.autoSkip || o.source === 'auto') &&
          ((this.ticks = Ys(this, this.ticks)),
          (this._labelSizes = null),
          this.afterAutoSkip()),
      r && this._convertTicksToLabels(this.ticks),
      this.beforeFit(),
      this.fit(),
      this.afterFit(),
      this.afterUpdate());
    }

    configure() {
      let t;
      let e;
      let i = this.options.reverse;
      (this.isHorizontal() ?
        ((t = this.left), (e = this.right)) :
        ((t = this.top), (e = this.bottom), (i = !i)),
      (this._startPixel = t),
      (this._endPixel = e),
      (this._reversePixels = i),
      (this._length = e - t),
      (this._alignToPixels = this.options.alignToPixels));
    }

    afterUpdate() {
      d(this.options.afterUpdate, [this]);
    }

    beforeSetDimensions() {
      d(this.options.beforeSetDimensions, [this]);
    }

    setDimensions() {
      (this.isHorizontal() ?
        ((this.width = this.maxWidth),
        (this.left = 0),
        (this.right = this.width)) :
        ((this.height = this.maxHeight),
        (this.top = 0),
        (this.bottom = this.height)),
      (this.paddingLeft = 0),
      (this.paddingTop = 0),
      (this.paddingRight = 0),
      (this.paddingBottom = 0));
    }

    afterSetDimensions() {
      d(this.options.afterSetDimensions, [this]);
    }

    _callHooks(t) {
      (this.chart.notifyPlugins(t, this.getContext()),
      d(this.options[t], [this]));
    }

    beforeDataLimits() {
      this._callHooks('beforeDataLimits');
    }

    determineDataLimits() {}
    afterDataLimits() {
      this._callHooks('afterDataLimits');
    }

    beforeBuildTicks() {
      this._callHooks('beforeBuildTicks');
    }

    buildTicks() {
      return [];
    }

    afterBuildTicks() {
      this._callHooks('afterBuildTicks');
    }

    beforeTickToLabelConversion() {
      d(this.options.beforeTickToLabelConversion, [this]);
    }

    generateTickLabels(t) {
      const e = this.options.ticks;
      let i, s, n;
      for (i = 0, s = t.length; i < s; i++) {
        ((n = t[i]), (n.label = d(e.callback, [n.value, i, t], this)));
      }
    }

    afterTickToLabelConversion() {
      d(this.options.afterTickToLabelConversion, [this]);
    }

    beforeCalculateLabelRotation() {
      d(this.options.beforeCalculateLabelRotation, [this]);
    }

    calculateLabelRotation() {
      const t = this.options;
      const e = t.ticks;
      const i = qs(this.ticks.length, t.ticks.maxTicksLimit);
      const s = e.minRotation || 0;
      const n = e.maxRotation;
      let o;
      let a;
      let r;
      let l = s;
      if (
        !this._isVisible() ||
        !e.display ||
        s >= n ||
        i <= 1 ||
        !this.isHorizontal()
      ) {
        return void (this.labelRotation = s);
      }
      const h = this._getLabelSizes();
      const c = h.widest.width;
      const d = h.highest.height;
      const u = Z(this.chart.width - c, 0, this.maxWidth);
      ((o = t.offset ? this.maxWidth / i : u / (i - 1)),
      c + 6 > o &&
          ((o = u / (i - (t.offset ? 0.5 : 1))),
          (a =
            this.maxHeight -
            Js(t.grid) -
            e.padding -
            Zs(t.title, this.chart.options.font)),
          (r = Math.sqrt(c * c + d * d)),
          (l = Y(
            Math.min(
              Math.asin(Z((h.highest.height + 6) / o, -1, 1)),
              Math.asin(Z(a / r, -1, 1)) - Math.asin(Z(d / r, -1, 1)),
            ),
          )),
          (l = Math.max(s, Math.min(n, l)))),
      (this.labelRotation = l));
    }

    afterCalculateLabelRotation() {
      d(this.options.afterCalculateLabelRotation, [this]);
    }

    afterAutoSkip() {}
    beforeFit() {
      d(this.options.beforeFit, [this]);
    }

    fit() {
      const t = { width: 0, height: 0 };
      const {
        chart: e,
        options: { ticks: i, title: s, grid: n },
      } = this;
      const o = this._isVisible();
      const a = this.isHorizontal();
      if (o) {
        const o = Zs(s, e.options.font);
        if (
          (a ?
            ((t.width = this.maxWidth), (t.height = Js(n) + o)) :
            ((t.height = this.maxHeight), (t.width = Js(n) + o)),
          i.display && this.ticks.length)
        ) {
          const {
            first: e,
            last: s,
            widest: n,
            highest: o,
          } = this._getLabelSizes();
          const r = 2 * i.padding;
          const l = $(this.labelRotation);
          const h = Math.cos(l);
          const c = Math.sin(l);
          if (a) {
            const e = i.mirror ? 0 : c * n.width + h * o.height;
            t.height = Math.min(this.maxHeight, t.height + e + r);
          } else {
            const e = i.mirror ? 0 : h * n.width + c * o.height;
            t.width = Math.min(this.maxWidth, t.width + e + r);
          }
          this._calculatePadding(e, s, c, h);
        }
      }
      (this._handleMargins(),
      a ?
        ((this.width = this._length =
              e.width - this._margins.left - this._margins.right),
        (this.height = t.height)) :
        ((this.width = t.width),
        (this.height = this._length =
              e.height - this._margins.top - this._margins.bottom)));
    }

    _calculatePadding(t, e, i, s) {
      const {
        ticks: { align: n, padding: o },
        position: a,
      } = this.options;
      const r = this.labelRotation !== 0;
      const l = a !== 'top' && this.axis === 'x';
      if (this.isHorizontal()) {
        const a = this.getPixelForTick(0) - this.left;
        const h = this.right - this.getPixelForTick(this.ticks.length - 1);
        let c = 0;
        let d = 0;
        (r ?
          l ?
            ((c = s * t.width), (d = i * e.height)) :
            ((c = i * t.height), (d = s * e.width)) :
          n === 'start' ?
            (d = e.width) :
            n === 'end' ?
              (c = t.width) :
              n !== 'inner' && ((c = t.width / 2), (d = e.width / 2)),
        (this.paddingLeft = Math.max(
          ((c - a + o) * this.width) / (this.width - a),
          0,
        )),
        (this.paddingRight = Math.max(
          ((d - h + o) * this.width) / (this.width - h),
          0,
        )));
      } else {
        let i = e.height / 2;
        let s = t.height / 2;
        (n === 'start' ?
          ((i = 0), (s = t.height)) :
          n === 'end' && ((i = e.height), (s = 0)),
        (this.paddingTop = i + o),
        (this.paddingBottom = s + o));
      }
    }

    _handleMargins() {
      this._margins &&
        ((this._margins.left = Math.max(this.paddingLeft, this._margins.left)),
        (this._margins.top = Math.max(this.paddingTop, this._margins.top)),
        (this._margins.right = Math.max(
          this.paddingRight,
          this._margins.right,
        )),
        (this._margins.bottom = Math.max(
          this.paddingBottom,
          this._margins.bottom,
        )));
    }

    afterFit() {
      d(this.options.afterFit, [this]);
    }

    isHorizontal() {
      const { axis: t, position: e } = this.options;
      return e === 'top' || e === 'bottom' || t === 'x';
    }

    isFullSize() {
      return this.options.fullSize;
    }

    _convertTicksToLabels(t) {
      let e, i;
      for (
        this.beforeTickToLabelConversion(),
        this.generateTickLabels(t),
        e = 0,
        i = t.length;
        e < i;
        e++
      ) {
        s(t[e].label) && (t.splice(e, 1), i--, e--);
      }
      this.afterTickToLabelConversion();
    }

    _getLabelSizes() {
      let t = this._labelSizes;
      if (!t) {
        const e = this.options.ticks.sampleSize;
        let i = this.ticks;
        (e < i.length && (i = Ks(i, e)),
        (this._labelSizes = t =
            this._computeLabelSizes(
              i,
              i.length,
              this.options.ticks.maxTicksLimit,
            )));
      }
      return t;
    }

    _computeLabelSizes(t, e, i) {
      const { ctx: o, _longestTextCache: a } = this;
      const r = [];
      const l = [];
      const h = Math.floor(e / qs(e, i));
      let c;
      let d;
      let f;
      let g;
      let p;
      let m;
      let x;
      let b;
      let _;
      let y;
      let v;
      let M = 0;
      let w = 0;
      for (c = 0; c < e; c += h) {
        if (
          ((g = t[c].label),
          (p = this._resolveTickFontOptions(c)),
          (o.font = m = p.string),
          (x = a[m] = a[m] || { data: {}, gc: [] }),
          (b = p.lineHeight),
          (_ = y = 0),
          s(g) || n(g))
        ) {
          if (n(g)) {
            for (d = 0, f = g.length; d < f; ++d) {
              ((v = g[d]),
              s(v) || n(v) || ((_ = Ce(o, x.data, x.gc, _, v)), (y += b)));
            }
          }
        } else {
          ((_ = Ce(o, x.data, x.gc, _, g)), (y = b));
        }
        (r.push(_), l.push(y), (M = Math.max(_, M)), (w = Math.max(y, w)));
      }
      !(function(t, e) {
        u(t, t => {
          const i = t.gc;
          const s = i.length / 2;
          let n;
          if (s > e) {
            for (n = 0; n < s; ++n) {
              delete t.data[i[n]];
            }
            i.splice(0, s);
          }
        });
      })(a, e);
      const k = r.indexOf(M);
      const S = l.indexOf(w);
      const P = t => ({ width: r[t] || 0, height: l[t] || 0 });
      return {
        first: P(0),
        last: P(e - 1),
        widest: P(k),
        highest: P(S),
        widths: r,
        heights: l,
      };
    }

    getLabelForValue(t) {
      return t;
    }

    getPixelForValue(t, e) {
      return NaN;
    }

    getValueForPixel(t) {}
    getPixelForTick(t) {
      const e = this.ticks;
      return t < 0 || t > e.length - 1 ?
        null :
        this.getPixelForValue(e[t].value);
    }

    getPixelForDecimal(t) {
      this._reversePixels && (t = 1 - t);
      const e = this._startPixel + t * this._length;
      return Q(this._alignToPixels ? Ae(this.chart, e, 0) : e);
    }

    getDecimalForPixel(t) {
      const e = (t - this._startPixel) / this._length;
      return this._reversePixels ? 1 - e : e;
    }

    getBasePixel() {
      return this.getPixelForValue(this.getBaseValue());
    }

    getBaseValue() {
      const { min: t, max: e } = this;
      return t < 0 && e < 0 ? e : t > 0 && e > 0 ? t : 0;
    }

    getContext(t) {
      const e = this.ticks || [];
      if (t >= 0 && t < e.length) {
        const i = e[t];
        return (
          i.$context ||
          (i.$context = (function(t, e, i) {
            return Ci(t, { tick: i, index: e, type: 'tick' });
          })(this.getContext(), t, i))
        );
      }
      return (
        this.$context ||
        (this.$context = Ci(this.chart.getContext(), {
          scale: this,
          type: 'scale',
        }))
      );
    }

    _tickSize() {
      const t = this.options.ticks;
      const e = $(this.labelRotation);
      const i = Math.abs(Math.cos(e));
      const s = Math.abs(Math.sin(e));
      const n = this._getLabelSizes();
      const o = t.autoSkipPadding || 0;
      const a = n ? n.widest.width + o : 0;
      const r = n ? n.highest.height + o : 0;
      return this.isHorizontal() ?
        r * i > a * s ?
          a / i :
          r / s :
        r * s < a * i ?
          r / i :
          a / s;
    }

    _isVisible() {
      const t = this.options.display;
      return t !== 'auto' ? !!t : this.getMatchingVisibleMetas().length > 0;
    }

    _computeGridLineItems(t) {
      const e = this.axis;
      const i = this.chart;
      const s = this.options;
      const { grid: n, position: a, border: r } = s;
      const h = n.offset;
      const c = this.isHorizontal();
      const d = this.ticks.length + (h ? 1 : 0);
      const u = Js(n);
      const f = [];
      const g = r.setContext(this.getContext());
      const p = g.display ? g.width : 0;
      const m = p / 2;
      const x = function(t) {
        return Ae(i, t, p);
      };
      let b, _, y, v, M, w, k, S, P, D, C, O;
      if (a === 'top') {
        ((b = x(this.bottom)),
        (w = this.bottom - u),
        (S = b - m),
        (D = x(t.top) + m),
        (O = t.bottom));
      } else if (a === 'bottom') {
        ((b = x(this.top)),
        (D = t.top),
        (O = x(t.bottom) - m),
        (w = b + m),
        (S = this.top + u));
      } else if (a === 'left') {
        ((b = x(this.right)),
        (M = this.right - u),
        (k = b - m),
        (P = x(t.left) + m),
        (C = t.right));
      } else if (a === 'right') {
        ((b = x(this.left)),
        (P = t.left),
        (C = x(t.right) - m),
        (M = b + m),
        (k = this.left + u));
      } else if (e === 'x') {
        if (a === 'center') {
          b = x((t.top + t.bottom) / 2 + 0.5);
        } else if (o(a)) {
          const t = Object.keys(a)[0];
          const e = a[t];
          b = x(this.chart.scales[t].getPixelForValue(e));
        }
        ((D = t.top), (O = t.bottom), (w = b + m), (S = w + u));
      } else if (e === 'y') {
        if (a === 'center') {
          b = x((t.left + t.right) / 2);
        } else if (o(a)) {
          const t = Object.keys(a)[0];
          const e = a[t];
          b = x(this.chart.scales[t].getPixelForValue(e));
        }
        ((M = b - m), (k = M - u), (P = t.left), (C = t.right));
      }
      const A = l(s.ticks.maxTicksLimit, d);
      const T = Math.max(1, Math.ceil(d / A));
      for (_ = 0; _ < d; _ += T) {
        const t = this.getContext(_);
        const e = n.setContext(t);
        const s = r.setContext(t);
        const o = e.lineWidth;
        const a = e.color;
        const l = s.dash || [];
        const d = s.dashOffset;
        const u = e.tickWidth;
        const g = e.tickColor;
        const p = e.tickBorderDash || [];
        const m = e.tickBorderDashOffset;
        ((y = Gs(this, _, h)),
        void 0 !== y &&
            ((v = Ae(i, y, o)),
            c ? (M = k = P = C = v) : (w = S = D = O = v),
            f.push({
              tx1: M,
              ty1: w,
              tx2: k,
              ty2: S,
              x1: P,
              y1: D,
              x2: C,
              y2: O,
              width: o,
              color: a,
              borderDash: l,
              borderDashOffset: d,
              tickWidth: u,
              tickColor: g,
              tickBorderDash: p,
              tickBorderDashOffset: m,
            })));
      }
      return ((this._ticksLength = d), (this._borderValue = b), f);
    }

    _computeLabelItems(t) {
      const e = this.axis;
      const i = this.options;
      const { position: s, ticks: a } = i;
      const r = this.isHorizontal();
      const l = this.ticks;
      const { align: h, crossAlign: c, padding: d, mirror: u } = a;
      const f = Js(i.grid);
      const g = f + d;
      const p = u ? -d : g;
      const m = -$(this.labelRotation);
      const x = [];
      let b;
      let _;
      let y;
      let v;
      let M;
      let w;
      let k;
      let S;
      let P;
      let D;
      let C;
      let O;
      let A = 'middle';
      if (s === 'top') {
        ((w = this.bottom - p), (k = this._getXAxisLabelAlignment()));
      } else if (s === 'bottom') {
        ((w = this.top + p), (k = this._getXAxisLabelAlignment()));
      } else if (s === 'left') {
        const t = this._getYAxisLabelAlignment(f);
        ((k = t.textAlign), (M = t.x));
      } else if (s === 'right') {
        const t = this._getYAxisLabelAlignment(f);
        ((k = t.textAlign), (M = t.x));
      } else if (e === 'x') {
        if (s === 'center') {
          w = (t.top + t.bottom) / 2 + g;
        } else if (o(s)) {
          const t = Object.keys(s)[0];
          const e = s[t];
          w = this.chart.scales[t].getPixelForValue(e) + g;
        }
        k = this._getXAxisLabelAlignment();
      } else if (e === 'y') {
        if (s === 'center') {
          M = (t.left + t.right) / 2 - g;
        } else if (o(s)) {
          const t = Object.keys(s)[0];
          const e = s[t];
          M = this.chart.scales[t].getPixelForValue(e);
        }
        k = this._getYAxisLabelAlignment(f).textAlign;
      }
      e === 'y' &&
        (h === 'start' ? (A = 'top') : h === 'end' && (A = 'bottom'));
      const T = this._getLabelSizes();
      for (b = 0, _ = l.length; b < _; ++b) {
        ((y = l[b]), (v = y.label));
        const t = a.setContext(this.getContext(b));
        ((S = this.getPixelForTick(b) + a.labelOffset),
        (P = this._resolveTickFontOptions(b)),
        (D = P.lineHeight),
        (C = n(v) ? v.length : 1));
        const e = C / 2;
        const i = t.color;
        const o = t.textStrokeColor;
        const h = t.textStrokeWidth;
        let d;
        let f = k;
        if (
          (r ?
            ((M = S),
            k === 'inner' &&
                (f =
                  b === _ - 1 ?
                    this.options.reverse ?
                      'left' :
                      'right' :
                    b === 0 ?
                      this.options.reverse ?
                        'right' :
                        'left' :
                      'center'),
            (O =
                s === 'top' ?
                  c === 'near' || m !== 0 ?
                    -C * D + D / 2 :
                    c === 'center' ?
                      -T.highest.height / 2 - e * D + D :
                      -T.highest.height + D / 2 :
                  c === 'near' || m !== 0 ?
                    D / 2 :
                    c === 'center' ?
                      T.highest.height / 2 - e * D :
                      T.highest.height - C * D),
            u && (O *= -1),
            m === 0 || t.showLabelBackdrop || (M += (D / 2) * Math.sin(m))) :
            ((w = S), (O = ((1 - C) * D) / 2)),
          t.showLabelBackdrop)
        ) {
          const e = ki(t.backdropPadding);
          const i = T.heights[b];
          const s = T.widths[b];
          let n = O - e.top;
          let o = 0 - e.left;
          switch (A) {
          case 'middle':
            n -= i / 2;
            break;
          case 'bottom':
            n -= i;
          }
          switch (k) {
          case 'center':
            o -= s / 2;
            break;
          case 'right':
            o -= s;
            break;
          case 'inner':
            b === _ - 1 ? (o -= s) : b > 0 && (o -= s / 2);
          }
          d = {
            left: o,
            top: n,
            width: s + e.width,
            height: i + e.height,
            color: t.backdropColor,
          };
        }
        x.push({
          label: v,
          font: P,
          textOffset: O,
          options: {
            rotation: m,
            color: i,
            strokeColor: o,
            strokeWidth: h,
            textAlign: f,
            textBaseline: A,
            translation: [M, w],
            backdrop: d,
          },
        });
      }
      return x;
    }

    _getXAxisLabelAlignment() {
      const { position: t, ticks: e } = this.options;
      if (-$(this.labelRotation)) {
        return t === 'top' ? 'left' : 'right';
      }
      let i = 'center';
      return (
        e.align === 'start' ?
          (i = 'left') :
          e.align === 'end' ?
            (i = 'right') :
            e.align === 'inner' && (i = 'inner'),
        i
      );
    }

    _getYAxisLabelAlignment(t) {
      const {
        position: e,
        ticks: { crossAlign: i, mirror: s, padding: n },
      } = this.options;
      const o = t + n;
      const a = this._getLabelSizes().widest.width;
      let r, l;
      return (
        e === 'left' ?
          s ?
            ((l = this.right + n),
            i === 'near' ?
              (r = 'left') :
              i === 'center' ?
                ((r = 'center'), (l += a / 2)) :
                ((r = 'right'), (l += a))) :
            ((l = this.right - o),
            i === 'near' ?
              (r = 'right') :
              i === 'center' ?
                ((r = 'center'), (l -= a / 2)) :
                ((r = 'left'), (l = this.left))) :
          e === 'right' ?
            s ?
              ((l = this.left + n),
              i === 'near' ?
                (r = 'right') :
                i === 'center' ?
                  ((r = 'center'), (l -= a / 2)) :
                  ((r = 'left'), (l -= a))) :
              ((l = this.left + o),
              i === 'near' ?
                (r = 'left') :
                i === 'center' ?
                  ((r = 'center'), (l += a / 2)) :
                  ((r = 'right'), (l = this.right))) :
            (r = 'right'),
        { textAlign: r, x: l }
      );
    }

    _computeLabelArea() {
      if (this.options.ticks.mirror) {
        return;
      }
      const t = this.chart;
      const e = this.options.position;
      return e === 'left' || e === 'right' ?
        { top: 0, left: this.left, bottom: t.height, right: this.right } :
        e === 'top' || e === 'bottom' ?
          { top: this.top, left: 0, bottom: this.bottom, right: t.width } :
          void 0;
    }

    drawBackground() {
      const {
        ctx: t,
        options: { backgroundColor: e },
        left: i,
        top: s,
        width: n,
        height: o,
      } = this;
      e && (t.save(), (t.fillStyle = e), t.fillRect(i, s, n, o), t.restore());
    }

    getLineWidthForValue(t) {
      const e = this.options.grid;
      if (!this._isVisible() || !e.display) {
        return 0;
      }
      const i = this.ticks.findIndex(e => e.value === t);
      if (i >= 0) {
        return e.setContext(this.getContext(i)).lineWidth;
      }
      return 0;
    }

    drawGrid(t) {
      const e = this.options.grid;
      const i = this.ctx;
      const s =
          this._gridLineItems ||
          (this._gridLineItems = this._computeGridLineItems(t));
      let n, o;
      const a = (t, e, s) => {
        s.width &&
          s.color &&
          (i.save(),
          (i.lineWidth = s.width),
          (i.strokeStyle = s.color),
          i.setLineDash(s.borderDash || []),
          (i.lineDashOffset = s.borderDashOffset),
          i.beginPath(),
          i.moveTo(t.x, t.y),
          i.lineTo(e.x, e.y),
          i.stroke(),
          i.restore());
      };
      if (e.display) {
        for (n = 0, o = s.length; n < o; ++n) {
          const t = s[n];
          (e.drawOnChartArea &&
            a({ x: t.x1, y: t.y1 }, { x: t.x2, y: t.y2 }, t),
          e.drawTicks &&
              a(
                { x: t.tx1, y: t.ty1 },
                { x: t.tx2, y: t.ty2 },
                {
                  color: t.tickColor,
                  width: t.tickWidth,
                  borderDash: t.tickBorderDash,
                  borderDashOffset: t.tickBorderDashOffset,
                },
              ));
        }
      }
    }

    drawBorder() {
      const {
        chart: t,
        ctx: e,
        options: { border: i, grid: s },
      } = this;
      const n = i.setContext(this.getContext());
      const o = i.display ? n.width : 0;
      if (!o) {
        return;
      }
      const a = s.setContext(this.getContext(0)).lineWidth;
      const r = this._borderValue;
      let l, h, c, d;
      (this.isHorizontal() ?
        ((l = Ae(t, this.left, o) - o / 2),
        (h = Ae(t, this.right, a) + a / 2),
        (c = d = r)) :
        ((c = Ae(t, this.top, o) - o / 2),
        (d = Ae(t, this.bottom, a) + a / 2),
        (l = h = r)),
      e.save(),
      (e.lineWidth = n.width),
      (e.strokeStyle = n.color),
      e.beginPath(),
      e.moveTo(l, c),
      e.lineTo(h, d),
      e.stroke(),
      e.restore());
    }

    drawLabels(t) {
      if (!this.options.ticks.display) {
        return;
      }
      const e = this.ctx;
      const i = this._computeLabelArea();
      i && Ie(e, i);
      const s = this.getLabelItems(t);
      for (const t of s) {
        const i = t.options;
        const s = t.font;
        Ne(e, t.label, 0, t.textOffset, s, i);
      }
      i && ze(e);
    }

    drawTitle() {
      const {
        ctx: t,
        options: { position: e, title: i, reverse: s },
      } = this;
      if (!i.display) {
        return;
      }
      const a = Si(i.font);
      const r = ki(i.padding);
      const l = i.align;
      let h = a.lineHeight / 2;
      e === 'bottom' || e === 'center' || o(e) ?
        ((h += r.bottom),
        n(i.text) && (h += a.lineHeight * (i.text.length - 1))) :
        (h += r.top);
      const {
        titleX: c,
        titleY: d,
        maxWidth: u,
        rotation: f,
      } = (function(t, e, i, s) {
        const { top: n, left: a, bottom: r, right: l, chart: h } = t;
        const { chartArea: c, scales: d } = h;
        let u;
        let f;
        let g;
        let p = 0;
        const m = r - n;
        const x = l - a;
        if (t.isHorizontal()) {
          if (((f = ft(s, a, l)), o(i))) {
            const t = Object.keys(i)[0];
            const s = i[t];
            g = d[t].getPixelForValue(s) + m - e;
          } else {
            g = i === 'center' ? (c.bottom + c.top) / 2 + m - e : Xs(t, i, e);
          }
          u = l - a;
        } else {
          if (o(i)) {
            const t = Object.keys(i)[0];
            const s = i[t];
            f = d[t].getPixelForValue(s) - x + e;
          } else {
            f = i === 'center' ? (c.left + c.right) / 2 - x + e : Xs(t, i, e);
          }
          ((g = ft(s, r, n)), (p = i === 'left' ? -E : E));
        }
        return { titleX: f, titleY: g, maxWidth: u, rotation: p };
      })(this, h, e, l);
      Ne(t, i.text, 0, 0, a, {
        color: i.color,
        maxWidth: u,
        rotation: f,
        textAlign: Qs(l, e, s),
        textBaseline: 'middle',
        translation: [c, d],
      });
    }

    draw(t) {
      this._isVisible() &&
        (this.drawBackground(),
        this.drawGrid(t),
        this.drawBorder(),
        this.drawTitle(),
        this.drawLabels(t));
    }

    _layers() {
      const t = this.options;
      const e = (t.ticks && t.ticks.z) || 0;
      const i = l(t.grid && t.grid.z, -1);
      const s = l(t.border && t.border.z, 0);
      return this._isVisible() && this.draw === tn.prototype.draw ?
        [
          {
            z: i,
            draw: t => {
              (this.drawBackground(), this.drawGrid(t), this.drawTitle());
            },
          },
          {
            z: s,
            draw: () => {
              this.drawBorder();
            },
          },
          {
            z: e,
            draw: t => {
              this.drawLabels(t);
            },
          },
        ] :
        [
          {
            z: e,
            draw: t => {
              this.draw(t);
            },
          },
        ];
    }

    getMatchingVisibleMetas(t) {
      const e = this.chart.getSortedVisibleDatasetMetas();
      const i = `${this.axis}AxisID`;
      const s = [];
      let n, o;
      for (n = 0, o = e.length; n < o; ++n) {
        const o = e[n];
        o[i] !== this.id || (t && o.type !== t) || s.push(o);
      }
      return s;
    }

    _resolveTickFontOptions(t) {
      return Si(this.options.ticks.setContext(this.getContext(t)).font);
    }

    _maxDigits() {
      const t = this._resolveTickFontOptions(0).lineHeight;
      return (this.isHorizontal() ? this.width : this.height) / t;
    }
  }
  class en {
    constructor(t, e, i) {
      ((this.type = t),
      (this.scope = e),
      (this.override = i),
      (this.items = Object.create(null)));
    }

    isForType(t) {
      return Object.prototype.isPrototypeOf.call(
        this.type.prototype,
        t.prototype,
      );
    }

    register(t) {
      const e = Object.getPrototypeOf(t);
      let i;
      (function(t) {
        return 'id' in t && 'defaults' in t;
      })(e) && (i = this.register(e));
      const s = this.items;
      const n = t.id;
      const o = `${this.scope}.${n}`;
      if (!n) {
        throw new Error(`class does not have id: ${t}`);
      }
      return (
        n in s ||
          ((s[n] = t),
          (function(t, e, i) {
            const s = x(Object.create(null), [
              i ? ue.get(i) : {},
              ue.get(e),
              t.defaults,
            ]);
            (ue.set(e, s),
            t.defaultRoutes &&
                (function(t, e) {
                  Object.keys(e).forEach(i => {
                    const s = i.split('.');
                    const n = s.pop();
                    const o = [t].concat(s).join('.');
                    const a = e[i].split('.');
                    const r = a.pop();
                    const l = a.join('.');
                    ue.route(o, n, l, r);
                  });
                })(e, t.defaultRoutes));
            t.descriptors && ue.describe(e, t.descriptors);
          })(t, o, i),
          this.override && ue.override(t.id, t.overrides)),
        o
      );
    }

    get(t) {
      return this.items[t];
    }

    unregister(t) {
      const e = this.items;
      const i = t.id;
      const s = this.scope;
      (i in e && delete e[i],
      s && i in ue[s] && (delete ue[s][i], this.override && delete re[i]));
    }
  }
  class sn {
    constructor() {
      ((this.controllers = new en(js, 'datasets', !0)),
      (this.elements = new en($s, 'elements')),
      (this.plugins = new en(Object, 'plugins')),
      (this.scales = new en(tn, 'scales')),
      (this._typedRegistries = [
        this.controllers,
        this.scales,
        this.elements,
      ]));
    }

    add(...t) {
      this._each('register', t);
    }

    remove(...t) {
      this._each('unregister', t);
    }

    addControllers(...t) {
      this._each('register', t, this.controllers);
    }

    addElements(...t) {
      this._each('register', t, this.elements);
    }

    addPlugins(...t) {
      this._each('register', t, this.plugins);
    }

    addScales(...t) {
      this._each('register', t, this.scales);
    }

    getController(t) {
      return this._get(t, this.controllers, 'controller');
    }

    getElement(t) {
      return this._get(t, this.elements, 'element');
    }

    getPlugin(t) {
      return this._get(t, this.plugins, 'plugin');
    }

    getScale(t) {
      return this._get(t, this.scales, 'scale');
    }

    removeControllers(...t) {
      this._each('unregister', t, this.controllers);
    }

    removeElements(...t) {
      this._each('unregister', t, this.elements);
    }

    removePlugins(...t) {
      this._each('unregister', t, this.plugins);
    }

    removeScales(...t) {
      this._each('unregister', t, this.scales);
    }

    _each(t, e, i) {
      [...e].forEach(e => {
        const s = i || this._getRegistryForType(e);
        i || s.isForType(e) || (s === this.plugins && e.id) ?
          this._exec(t, s, e) :
          u(e, e => {
            const s = i || this._getRegistryForType(e);
            this._exec(t, s, e);
          });
      });
    }

    _exec(t, e, i) {
      const s = w(t);
      (d(i[`before${s}`], [], i), e[t](i), d(i[`after${s}`], [], i));
    }

    _getRegistryForType(t) {
      for (let e = 0; e < this._typedRegistries.length; e++) {
        const i = this._typedRegistries[e];
        if (i.isForType(t)) {
          return i;
        }
      }
      return this.plugins;
    }

    _get(t, e, i) {
      const s = e.get(t);
      if (void 0 === s) {
        throw new Error(`"${t}" is not a registered ${i}.`);
      }
      return s;
    }
  }
  const nn = new sn();
  class on {
    constructor() {
      this._init = [];
    }

    notify(t, e, i, s) {
      e === 'beforeInit' &&
        ((this._init = this._createDescriptors(t, !0)),
        this._notify(this._init, t, 'install'));
      const n = s ? this._descriptors(t).filter(s) : this._descriptors(t);
      const o = this._notify(n, t, e, i);
      return (
        e === 'afterDestroy' &&
          (this._notify(n, t, 'stop'),
          this._notify(this._init, t, 'uninstall')),
        o
      );
    }

    _notify(t, e, i, s) {
      s = s || {};
      for (const n of t) {
        const t = n.plugin;
        if (!1 === d(t[i], [e, s, n.options], t) && s.cancelable) {
          return !1;
        }
      }
      return !0;
    }

    invalidate() {
      s(this._cache) ||
        ((this._oldCache = this._cache), (this._cache = void 0));
    }

    _descriptors(t) {
      if (this._cache) {
        return this._cache;
      }
      const e = (this._cache = this._createDescriptors(t));
      return (this._notifyStateChanges(t), e);
    }

    _createDescriptors(t, e) {
      const i = t && t.config;
      const s = l(i.options && i.options.plugins, {});
      const n = (function(t) {
        const e = {};
        const i = [];
        const s = Object.keys(nn.plugins.items);
        for (let t = 0; t < s.length; t++) {
          i.push(nn.getPlugin(s[t]));
        }
        const n = t.plugins || [];
        for (let t = 0; t < n.length; t++) {
          const s = n[t];
          i.indexOf(s) === -1 && (i.push(s), (e[s.id] = !0));
        }
        return { plugins: i, localIds: e };
      })(i);
      return !1 !== s || e ?
        (function(t, { plugins: e, localIds: i }, s, n) {
          const o = [];
          const a = t.getContext();
          for (const r of e) {
            const e = r.id;
            const l = an(s[e], n);
            l !== null &&
                o.push({
                  plugin: r,
                  options: rn(t.config, { plugin: r, local: i[e] }, l, a),
                });
          }
          return o;
        })(t, n, s, e) :
        [];
    }

    _notifyStateChanges(t) {
      const e = this._oldCache || [];
      const i = this._cache;
      const s = (t, e) =>
        t.filter(t => !e.some(e => t.plugin.id === e.plugin.id));
      (this._notify(s(e, i), t, 'stop'), this._notify(s(i, e), t, 'start'));
    }
  }
  function an(t, e) {
    return e || !1 !== t ? (!0 === t ? {} : t) : null;
  }
  function rn(t, { plugin: e, local: i }, s, n) {
    const o = t.pluginScopeKeys(e);
    const a = t.getOptionScopes(s, o);
    return (
      i && e.defaults && a.push(e.defaults),
      t.createResolver(a, n, [''], {
        scriptable: !1,
        indexable: !1,
        allKeys: !0,
      })
    );
  }
  function ln(t, e) {
    const i = ue.datasets[t] || {};
    return (
      ((e.datasets || {})[t] || {}).indexAxis ||
      e.indexAxis ||
      i.indexAxis ||
      'x'
    );
  }
  function hn(t) {
    if (t === 'x' || t === 'y' || t === 'r') {
      return t;
    }
  }
  function cn(t, ...e) {
    if (hn(t)) {
      return t;
    }
    for (const s of e) {
      const e =
        s.axis ||
        ((i = s.position) === 'top' || i === 'bottom' ?
          'x' :
          i === 'left' || i === 'right' ?
            'y' :
            void 0) ||
        (t.length > 1 && hn(t[0].toLowerCase()));
      if (e) {
        return e;
      }
    }
    let i;
    throw new Error(
      `Cannot determine type of '${t}' axis. Please provide 'axis' or 'position' option.`,
    );
  }
  function dn(t, e, i) {
    if (i[`${e}AxisID`] === t) {
      return { axis: e };
    }
  }
  function un(t, e) {
    const i = re[t.type] || { scales: {} };
    const s = e.scales || {};
    const n = ln(t.type, e);
    const a = Object.create(null);
    return (
      Object.keys(s).forEach(e => {
        const r = s[e];
        if (!o(r)) {
          return console.error(`Invalid scale configuration for scale: ${e}`);
        }
        if (r._proxy) {
          return console.warn(
            `Ignoring resolver passed as options for scale: ${e}`,
          );
        }
        const l = cn(
          e,
          r,
          (function(t, e) {
            if (e.data && e.data.datasets) {
              const i = e.data.datasets.filter(
                e => e.xAxisID === t || e.yAxisID === t,
              );
              if (i.length) {
                return dn(t, 'x', i[0]) || dn(t, 'y', i[0]);
              }
            }
            return {};
          })(e, t),
          ue.scales[r.type],
        );
        const h = (function(t, e) {
          return t === e ? '_index_' : '_value_';
        })(l, n);
        const c = i.scales || {};
        a[e] = b(Object.create(null), [{ axis: l }, r, c[l], c[h]]);
      }),
      t.data.datasets.forEach(i => {
        const n = i.type || t.type;
        const o = i.indexAxis || ln(n, e);
        const r = (re[n] || {}).scales || {};
        Object.keys(r).forEach(t => {
          const e = (function(t, e) {
            let i = t;
            return (
              t === '_index_' ?
                (i = e) :
                t === '_value_' && (i = e === 'x' ? 'y' : 'x'),
              i
            );
          })(t, o);
          const n = i[`${e}AxisID`] || e;
          ((a[n] = a[n] || Object.create(null)),
          b(a[n], [{ axis: e }, s[n], r[t]]));
        });
      }),
      Object.keys(a).forEach(t => {
        const e = a[t];
        b(e, [ue.scales[e.type], ue.scale]);
      }),
      a
    );
  }
  function fn(t) {
    const e = t.options || (t.options = {});
    ((e.plugins = l(e.plugins, {})), (e.scales = un(t, e)));
  }
  function gn(t) {
    return (
      ((t = t || {}).datasets = t.datasets || []),
      (t.labels = t.labels || []),
      t
    );
  }
  const pn = new Map();
  const mn = new Set();
  function xn(t, e) {
    let i = pn.get(t);
    return (i || ((i = e()), pn.set(t, i), mn.add(i)), i);
  }
  const bn = (t, e, i) => {
    const s = M(e, i);
    void 0 !== s && t.add(s);
  };
  class _n {
    constructor(t) {
      ((this._config = (function(t) {
        return (((t = t || {}).data = gn(t.data)), fn(t), t);
      })(t)),
      (this._scopeCache = new Map()),
      (this._resolverCache = new Map()));
    }

    get platform() {
      return this._config.platform;
    }

    get type() {
      return this._config.type;
    }

    set type(t) {
      this._config.type = t;
    }

    get data() {
      return this._config.data;
    }

    set data(t) {
      this._config.data = gn(t);
    }

    get options() {
      return this._config.options;
    }

    set options(t) {
      this._config.options = t;
    }

    get plugins() {
      return this._config.plugins;
    }

    update() {
      const t = this._config;
      (this.clearCache(), fn(t));
    }

    clearCache() {
      (this._scopeCache.clear(), this._resolverCache.clear());
    }

    datasetScopeKeys(t) {
      return xn(t, () => [[`datasets.${t}`, '']]);
    }

    datasetAnimationScopeKeys(t, e) {
      return xn(`${t}.transition.${e}`, () => [
        [`datasets.${t}.transitions.${e}`, `transitions.${e}`],
        [`datasets.${t}`, ''],
      ]);
    }

    datasetElementScopeKeys(t, e) {
      return xn(`${t}-${e}`, () => [
        [`datasets.${t}.elements.${e}`, `datasets.${t}`, `elements.${e}`, ''],
      ]);
    }

    pluginScopeKeys(t) {
      const e = t.id;
      return xn(`${this.type}-plugin-${e}`, () => [
        [`plugins.${e}`, ...(t.additionalOptionScopes || [])],
      ]);
    }

    _cachedScopes(t, e) {
      const i = this._scopeCache;
      let s = i.get(t);
      return ((s && !e) || ((s = new Map()), i.set(t, s)), s);
    }

    getOptionScopes(t, e, i) {
      const { options: s, type: n } = this;
      const o = this._cachedScopes(t, i);
      const a = o.get(e);
      if (a) {
        return a;
      }
      const r = new Set();
      e.forEach(e => {
        (t && (r.add(t), e.forEach(e => bn(r, t, e))),
        e.forEach(t => bn(r, s, t)),
        e.forEach(t => bn(r, re[n] || {}, t)),
        e.forEach(t => bn(r, ue, t)),
        e.forEach(t => bn(r, le, t)));
      });
      const l = Array.from(r);
      return (
        l.length === 0 && l.push(Object.create(null)),
        mn.has(e) && o.set(e, l),
        l
      );
    }

    chartOptionScopes() {
      const { options: t, type: e } = this;
      return [t, re[e] || {}, ue.datasets[e] || {}, { type: e }, ue, le];
    }

    resolveNamedOptions(t, e, i, s = ['']) {
      const o = { $shared: !0 };
      const { resolver: a, subPrefixes: r } = yn(this._resolverCache, t, s);
      let l = a;
      if (
        (function(t, e) {
          const { isScriptable: i, isIndexable: s } = Ye(t);
          for (const o of e) {
            const e = i(o);
            const a = s(o);
            const r = (a || e) && t[o];
            if ((e && (S(r) || vn(r))) || (a && n(r))) {
              return !0;
            }
          }
          return !1;
        })(a, e)
      ) {
        o.$shared = !1;
        l = $e(a, (i = S(i) ? i() : i), this.createResolver(t, i, r));
      }
      for (const t of e) {
        o[t] = l[t];
      }
      return o;
    }

    createResolver(t, e, i = [''], s) {
      const { resolver: n } = yn(this._resolverCache, t, i);
      return o(e) ? $e(n, e, void 0, s) : n;
    }
  }
  function yn(t, e, i) {
    let s = t.get(e);
    s || ((s = new Map()), t.set(e, s));
    const n = i.join();
    let o = s.get(n);
    if (!o) {
      ((o = {
        resolver: je(e, i),
        subPrefixes: i.filter(t => !t.toLowerCase().includes('hover')),
      }),
      s.set(n, o));
    }
    return o;
  }
  const vn = t => o(t) && Object.getOwnPropertyNames(t).some(e => S(t[e]));
  const Mn = ['top', 'bottom', 'left', 'right', 'chartArea'];
  function wn(t, e) {
    return t === 'top' || t === 'bottom' || (Mn.indexOf(t) === -1 && e === 'x');
  }
  function kn(t, e) {
    return function(i, s) {
      return i[t] === s[t] ? i[e] - s[e] : i[t] - s[t];
    };
  }
  function Sn(t) {
    const e = t.chart;
    const i = e.options.animation;
    (e.notifyPlugins('afterRender'), d(i && i.onComplete, [t], e));
  }
  function Pn(t) {
    const e = t.chart;
    const i = e.options.animation;
    d(i && i.onProgress, [t], e);
  }
  function Dn(t) {
    return (
      fe() && typeof t === 'string' ?
        (t = document.getElementById(t)) :
        t && t.length && (t = t[0]),
      t && t.canvas && (t = t.canvas),
      t
    );
  }
  const Cn = {};
  const On = t => {
    const e = Dn(t);
    return Object.values(Cn)
      .filter(t => t.canvas === e)
      .pop();
  };
  function An(t, e, i) {
    const s = Object.keys(t);
    for (const n of s) {
      const s = +n;
      if (s >= e) {
        const o = t[n];
        (delete t[n], (i > 0 || s > e) && (t[s + i] = o));
      }
    }
  }
  class Tn {
    static defaults = ue;
    static instances = Cn;
    static overrides = re;
    static registry = nn;
    static version = '4.5.0';
    static getChart = On;
    static register(...t) {
      (nn.add(...t), Ln());
    }

    static unregister(...t) {
      (nn.remove(...t), Ln());
    }

    constructor(t, e) {
      const s = (this.config = new _n(e));
      const n = Dn(t);
      const o = On(n);
      if (o) {
        throw new Error(
          `Canvas is already in use. Chart with ID '${
            o.id
          }' must be destroyed before the canvas with ID '${
            o.canvas.id
          }' can be reused.`,
        );
      }
      const a = s.createResolver(s.chartOptionScopes(), this.getContext());
      ((this.platform = new (s.platform || Ps(n))()),
      this.platform.updateConfig(s));
      const r = this.platform.acquireContext(n, a.aspectRatio);
      const l = r && r.canvas;
      const h = l && l.height;
      const c = l && l.width;
      ((this.id = i()),
      (this.ctx = r),
      (this.canvas = l),
      (this.width = c),
      (this.height = h),
      (this._options = a),
      (this._aspectRatio = this.aspectRatio),
      (this._layers = []),
      (this._metasets = []),
      (this._stacks = void 0),
      (this.boxes = []),
      (this.currentDevicePixelRatio = void 0),
      (this.chartArea = void 0),
      (this._active = []),
      (this._lastEvent = void 0),
      (this._listeners = {}),
      (this._responsiveListeners = void 0),
      (this._sortedMetasets = []),
      (this.scales = {}),
      (this._plugins = new on()),
      (this.$proxies = {}),
      (this._hiddenIndices = {}),
      (this.attached = !1),
      (this._animationsDisabled = void 0),
      (this.$context = void 0),
      (this._doResize = dt(t => this.update(t), a.resizeDelay || 0)),
      (this._dataChanges = []),
      (Cn[this.id] = this),
      r && l ?
        (bt.listen(this, 'complete', Sn),
        bt.listen(this, 'progress', Pn),
        this._initialize(),
        this.attached && this.update()) :
        console.error(
          'Failed to create chart: can\'t acquire context from the given item',
        ));
    }

    get aspectRatio() {
      const {
        options: { aspectRatio: t, maintainAspectRatio: e },
        width: i,
        height: n,
        _aspectRatio: o,
      } = this;
      return s(t) ? (e && o ? o : n ? i / n : null) : t;
    }

    get data() {
      return this.config.data;
    }

    set data(t) {
      this.config.data = t;
    }

    get options() {
      return this._options;
    }

    set options(t) {
      this.config.options = t;
    }

    get registry() {
      return nn;
    }

    _initialize() {
      return (
        this.notifyPlugins('beforeInit'),
        this.options.responsive ?
          this.resize() :
          ke(this, this.options.devicePixelRatio),
        this.bindEvents(),
        this.notifyPlugins('afterInit'),
        this
      );
    }

    clear() {
      return (Te(this.canvas, this.ctx), this);
    }

    stop() {
      return (bt.stop(this), this);
    }

    resize(t, e) {
      bt.running(this) ?
        (this._resizeBeforeDraw = { width: t, height: e }) :
        this._resize(t, e);
    }

    _resize(t, e) {
      const i = this.options;
      const s = this.canvas;
      const n = i.maintainAspectRatio && this.aspectRatio;
      const o = this.platform.getMaximumSize(s, t, e, n);
      const a = i.devicePixelRatio || this.platform.getDevicePixelRatio();
      const r = this.width ? 'resize' : 'attach';
      ((this.width = o.width),
      (this.height = o.height),
      (this._aspectRatio = this.aspectRatio),
      ke(this, a, !0) &&
          (this.notifyPlugins('resize', { size: o }),
          d(i.onResize, [this, o], this),
          this.attached && this._doResize(r) && this.render()));
    }

    ensureScalesHaveIDs() {
      u(this.options.scales || {}, (t, e) => {
        t.id = e;
      });
    }

    buildOrUpdateScales() {
      const t = this.options;
      const e = t.scales;
      const i = this.scales;
      const s = Object.keys(i).reduce((t, e) => ((t[e] = !1), t), {});
      let n = [];
      (e &&
        (n = n.concat(
          Object.keys(e).map(t => {
            const i = e[t];
            const s = cn(t, i);
            const n = s === 'r';
            const o = s === 'x';
            return {
              options: i,
              dposition: n ? 'chartArea' : o ? 'bottom' : 'left',
              dtype: n ? 'radialLinear' : o ? 'category' : 'linear',
            };
          }),
        )),
      u(n, e => {
        const n = e.options;
        const o = n.id;
        const a = cn(o, n);
        const r = l(n.type, e.dtype);
        ((void 0 !== n.position && wn(n.position, a) === wn(e.dposition)) ||
            (n.position = e.dposition),
        (s[o] = !0));
        let h = null;
        if (o in i && i[o].type === r) {
          h = i[o];
        } else {
          ((h = new (nn.getScale(r))({
            id: o,
            type: r,
            ctx: this.ctx,
            chart: this,
          })),
          (i[h.id] = h));
        }
        h.init(n, t);
      }),
      u(s, (t, e) => {
        t || delete i[e];
      }),
      u(i, t => {
        (ls.configure(this, t, t.options), ls.addBox(this, t));
      }));
    }

    _updateMetasets() {
      const t = this._metasets;
      const e = this.data.datasets.length;
      const i = t.length;
      if ((t.sort((t, e) => t.index - e.index), i > e)) {
        for (let t = e; t < i; ++t) {
          this._destroyDatasetMeta(t);
        }
        t.splice(e, i - e);
      }
      this._sortedMetasets = t.slice(0).sort(kn('order', 'index'));
    }

    _removeUnreferencedMetasets() {
      const {
        _metasets: t,
        data: { datasets: e },
      } = this;
      (t.length > e.length && delete this._stacks,
      t.forEach((t, i) => {
        e.filter(e => e === t._dataset).length === 0 &&
            this._destroyDatasetMeta(i);
      }));
    }

    buildOrUpdateControllers() {
      const t = [];
      const e = this.data.datasets;
      let i, s;
      for (
        this._removeUnreferencedMetasets(), i = 0, s = e.length;
        i < s;
        i++
      ) {
        const s = e[i];
        let n = this.getDatasetMeta(i);
        const o = s.type || this.config.type;
        if (
          (n.type &&
            n.type !== o &&
            (this._destroyDatasetMeta(i), (n = this.getDatasetMeta(i))),
          (n.type = o),
          (n.indexAxis = s.indexAxis || ln(o, this.options)),
          (n.order = s.order || 0),
          (n.index = i),
          (n.label = `${s.label}`),
          (n.visible = this.isDatasetVisible(i)),
          n.controller)
        ) {
          (n.controller.updateIndex(i), n.controller.linkScales());
        } else {
          const e = nn.getController(o);
          const { datasetElementType: s, dataElementType: a } = ue.datasets[o];
          (Object.assign(e, {
            dataElementType: nn.getElement(a),
            datasetElementType: s && nn.getElement(s),
          }),
          (n.controller = new e(this, i)),
          t.push(n.controller));
        }
      }
      return (this._updateMetasets(), t);
    }

    _resetElements() {
      u(
        this.data.datasets,
        (t, e) => {
          this.getDatasetMeta(e).controller.reset();
        },
        this,
      );
    }

    reset() {
      (this._resetElements(), this.notifyPlugins('reset'));
    }

    update(t) {
      const e = this.config;
      e.update();
      const i = (this._options = e.createResolver(
        e.chartOptionScopes(),
        this.getContext(),
      ));
      const s = (this._animationsDisabled = !i.animation);
      if (
        (this._updateScales(),
        this._checkEventBindings(),
        this._updateHiddenIndices(),
        this._plugins.invalidate(),
        !1 === this.notifyPlugins('beforeUpdate', { mode: t, cancelable: !0 }))
      ) {
        return;
      }
      const n = this.buildOrUpdateControllers();
      this.notifyPlugins('beforeElementsUpdate');
      let o = 0;
      for (let t = 0, e = this.data.datasets.length; t < e; t++) {
        const { controller: e } = this.getDatasetMeta(t);
        const i = !s && n.indexOf(e) === -1;
        (e.buildOrUpdateElements(i), (o = Math.max(+e.getMaxOverflow(), o)));
      }
      ((o = this._minPadding = i.layout.autoPadding ? o : 0),
      this._updateLayout(o),
      s ||
          u(n, t => {
            t.reset();
          }),
      this._updateDatasets(t),
      this.notifyPlugins('afterUpdate', { mode: t }),
      this._layers.sort(kn('z', '_idx')));
      const { _active: a, _lastEvent: r } = this;
      (r ?
        this._eventHandler(r, !0) :
        a.length && this._updateHoverStyles(a, a, !0),
      this.render());
    }

    _updateScales() {
      (u(this.scales, t => {
        ls.removeBox(this, t);
      }),
      this.ensureScalesHaveIDs(),
      this.buildOrUpdateScales());
    }

    _checkEventBindings() {
      const t = this.options;
      const e = new Set(Object.keys(this._listeners));
      const i = new Set(t.events);
      (P(e, i) && !!this._responsiveListeners === t.responsive) ||
        (this.unbindEvents(), this.bindEvents());
    }

    _updateHiddenIndices() {
      const { _hiddenIndices: t } = this;
      const e = this._getUniformDataChanges() || [];
      for (const { method: i, start: s, count: n } of e) {
        An(t, s, i === '_removeElements' ? -n : n);
      }
    }

    _getUniformDataChanges() {
      const t = this._dataChanges;
      if (!t || !t.length) {
        return;
      }
      this._dataChanges = [];
      const e = this.data.datasets.length;
      const i = e =>
        new Set(
          t
            .filter(t => t[0] === e)
            .map((t, e) => `${e},${t.splice(1).join(',')}`),
        );
      const s = i(0);
      for (let t = 1; t < e; t++) {
        if (!P(s, i(t))) {
          return;
        }
      }
      return Array.from(s)
        .map(t => t.split(','))
        .map(t => ({ method: t[1], start: +t[2], count: +t[3] }));
    }

    _updateLayout(t) {
      if (!1 === this.notifyPlugins('beforeLayout', { cancelable: !0 })) {
        return;
      }
      ls.update(this, this.width, this.height, t);
      const e = this.chartArea;
      const i = e.width <= 0 || e.height <= 0;
      ((this._layers = []),
      u(
        this.boxes,
        t => {
          (i && t.position === 'chartArea') ||
              (t.configure && t.configure(), this._layers.push(...t._layers()));
        },
        this,
      ),
      this._layers.forEach((t, e) => {
        t._idx = e;
      }),
      this.notifyPlugins('afterLayout'));
    }

    _updateDatasets(t) {
      if (
        !1 !==
        this.notifyPlugins('beforeDatasetsUpdate', { mode: t, cancelable: !0 })
      ) {
        for (let t = 0, e = this.data.datasets.length; t < e; ++t) {
          this.getDatasetMeta(t).controller.configure();
        }
        for (let e = 0, i = this.data.datasets.length; e < i; ++e) {
          this._updateDataset(e, S(t) ? t({ datasetIndex: e }) : t);
        }
        this.notifyPlugins('afterDatasetsUpdate', { mode: t });
      }
    }

    _updateDataset(t, e) {
      const i = this.getDatasetMeta(t);
      const s = { meta: i, index: t, mode: e, cancelable: !0 };
      !1 !== this.notifyPlugins('beforeDatasetUpdate', s) &&
        (i.controller._update(e),
        (s.cancelable = !1),
        this.notifyPlugins('afterDatasetUpdate', s));
    }

    render() {
      !1 !== this.notifyPlugins('beforeRender', { cancelable: !0 }) &&
        (bt.has(this) ?
          this.attached && !bt.running(this) && bt.start(this) :
          (this.draw(), Sn({ chart: this })));
    }

    draw() {
      let t;
      if (this._resizeBeforeDraw) {
        const { width: t, height: e } = this._resizeBeforeDraw;
        ((this._resizeBeforeDraw = null), this._resize(t, e));
      }
      if ((this.clear(), this.width <= 0 || this.height <= 0)) {
        return;
      }
      if (!1 === this.notifyPlugins('beforeDraw', { cancelable: !0 })) {
        return;
      }
      const e = this._layers;
      for (t = 0; t < e.length && e[t].z <= 0; ++t) {
        e[t].draw(this.chartArea);
      }
      for (this._drawDatasets(); t < e.length; ++t) {
        e[t].draw(this.chartArea);
      }
      this.notifyPlugins('afterDraw');
    }

    _getSortedDatasetMetas(t) {
      const e = this._sortedMetasets;
      const i = [];
      let s, n;
      for (s = 0, n = e.length; s < n; ++s) {
        const n = e[s];
        (t && !n.visible) || i.push(n);
      }
      return i;
    }

    getSortedVisibleDatasetMetas() {
      return this._getSortedDatasetMetas(!0);
    }

    _drawDatasets() {
      if (!1 === this.notifyPlugins('beforeDatasetsDraw', { cancelable: !0 })) {
        return;
      }
      const t = this.getSortedVisibleDatasetMetas();
      for (let e = t.length - 1; e >= 0; --e) {
        this._drawDataset(t[e]);
      }
      this.notifyPlugins('afterDatasetsDraw');
    }

    _drawDataset(t) {
      const e = this.ctx;
      const i = { meta: t, index: t.index, cancelable: !0 };
      const s = Ni(this, t);
      !1 !== this.notifyPlugins('beforeDatasetDraw', i) &&
        (s && Ie(e, s),
        t.controller.draw(),
        s && ze(e),
        (i.cancelable = !1),
        this.notifyPlugins('afterDatasetDraw', i));
    }

    isPointInArea(t) {
      return Re(t, this.chartArea, this._minPadding);
    }

    getElementsAtEventForMode(t, e, i, s) {
      const n = Ki.modes[e];
      return typeof n === 'function' ? n(this, t, i, s) : [];
    }

    getDatasetMeta(t) {
      const e = this.data.datasets[t];
      const i = this._metasets;
      let s = i.filter(t => t && t._dataset === e).pop();
      return (
        s ||
          ((s = {
            type: null,
            data: [],
            dataset: null,
            controller: null,
            hidden: null,
            xAxisID: null,
            yAxisID: null,
            order: (e && e.order) || 0,
            index: t,
            _dataset: e,
            _parsed: [],
            _sorted: !1,
          }),
          i.push(s)),
        s
      );
    }

    getContext() {
      return (
        this.$context ||
        (this.$context = Ci(null, { chart: this, type: 'chart' }))
      );
    }

    getVisibleDatasetCount() {
      return this.getSortedVisibleDatasetMetas().length;
    }

    isDatasetVisible(t) {
      const e = this.data.datasets[t];
      if (!e) {
        return !1;
      }
      const i = this.getDatasetMeta(t);
      return typeof i.hidden === 'boolean' ? !i.hidden : !e.hidden;
    }

    setDatasetVisibility(t, e) {
      this.getDatasetMeta(t).hidden = !e;
    }

    toggleDataVisibility(t) {
      this._hiddenIndices[t] = !this._hiddenIndices[t];
    }

    getDataVisibility(t) {
      return !this._hiddenIndices[t];
    }

    _updateVisibility(t, e, i) {
      const s = i ? 'show' : 'hide';
      const n = this.getDatasetMeta(t);
      const o = n.controller._resolveAnimations(void 0, s);
      k(e) ?
        ((n.data[e].hidden = !i), this.update()) :
        (this.setDatasetVisibility(t, i),
        o.update(n, { visible: i }),
        this.update(e => (e.datasetIndex === t ? s : void 0)));
    }

    hide(t, e) {
      this._updateVisibility(t, e, !1);
    }

    show(t, e) {
      this._updateVisibility(t, e, !0);
    }

    _destroyDatasetMeta(t) {
      const e = this._metasets[t];
      (e && e.controller && e.controller._destroy(), delete this._metasets[t]);
    }

    _stop() {
      let t, e;
      for (
        this.stop(), bt.remove(this), t = 0, e = this.data.datasets.length;
        t < e;
        ++t
      ) {
        this._destroyDatasetMeta(t);
      }
    }

    destroy() {
      this.notifyPlugins('beforeDestroy');
      const { canvas: t, ctx: e } = this;
      (this._stop(),
      this.config.clearCache(),
      t &&
          (this.unbindEvents(),
          Te(t, e),
          this.platform.releaseContext(e),
          (this.canvas = null),
          (this.ctx = null)),
      delete Cn[this.id],
      this.notifyPlugins('afterDestroy'));
    }

    toBase64Image(...t) {
      return this.canvas.toDataURL(...t);
    }

    bindEvents() {
      (this.bindUserEvents(),
      this.options.responsive ?
        this.bindResponsiveEvents() :
        (this.attached = !0));
    }

    bindUserEvents() {
      const t = this._listeners;
      const e = this.platform;
      const i = (i, s) => {
        (e.addEventListener(this, i, s), (t[i] = s));
      };
      const s = (t, e, i) => {
        ((t.offsetX = e), (t.offsetY = i), this._eventHandler(t));
      };
      u(this.options.events, t => i(t, s));
    }

    bindResponsiveEvents() {
      this._responsiveListeners || (this._responsiveListeners = {});
      const t = this._responsiveListeners;
      const e = this.platform;
      const i = (i, s) => {
        (e.addEventListener(this, i, s), (t[i] = s));
      };
      const s = (i, s) => {
        t[i] && (e.removeEventListener(this, i, s), delete t[i]);
      };
      const n = (t, e) => {
        this.canvas && this.resize(t, e);
      };
      let o;
      const a = () => {
        (s('attach', a),
        (this.attached = !0),
        this.resize(),
        i('resize', n),
        i('detach', o));
      };
      ((o = () => {
        ((this.attached = !1),
        s('resize', n),
        this._stop(),
        this._resize(0, 0),
        i('attach', a));
      }),
      e.isAttached(this.canvas) ? a() : o());
    }

    unbindEvents() {
      (u(this._listeners, (t, e) => {
        this.platform.removeEventListener(this, e, t);
      }),
      (this._listeners = {}),
      u(this._responsiveListeners, (t, e) => {
        this.platform.removeEventListener(this, e, t);
      }),
      (this._responsiveListeners = void 0));
    }

    updateHoverStyle(t, e, i) {
      const s = i ? 'set' : 'remove';
      let n, o, a, r;
      for (
        e === 'dataset' &&
          ((n = this.getDatasetMeta(t[0].datasetIndex)),
          n.controller[`_${s}DatasetHoverStyle`]()),
        a = 0,
        r = t.length;
        a < r;
        ++a
      ) {
        o = t[a];
        const e = o && this.getDatasetMeta(o.datasetIndex).controller;
        e && e[`${s}HoverStyle`](o.element, o.datasetIndex, o.index);
      }
    }

    getActiveElements() {
      return this._active || [];
    }

    setActiveElements(t) {
      const e = this._active || [];
      const i = t.map(({ datasetIndex: t, index: e }) => {
        const i = this.getDatasetMeta(t);
        if (!i) {
          throw new Error(`No dataset found at index ${t}`);
        }
        return { datasetIndex: t, element: i.data[e], index: e };
      });
      !f(i, e) &&
        ((this._active = i),
        (this._lastEvent = null),
        this._updateHoverStyles(i, e));
    }

    notifyPlugins(t, e, i) {
      return this._plugins.notify(this, t, e, i);
    }

    isPluginEnabled(t) {
      return this._plugins._cache.filter(e => e.plugin.id === t).length === 1;
    }

    _updateHoverStyles(t, e, i) {
      const s = this.options.hover;
      const n = (t, e) =>
        t.filter(
          t =>
            !e.some(
              e => t.datasetIndex === e.datasetIndex && t.index === e.index,
            ),
        );
      const o = n(e, t);
      const a = i ? t : n(t, e);
      (o.length && this.updateHoverStyle(o, s.mode, !1),
      a.length && s.mode && this.updateHoverStyle(a, s.mode, !0));
    }

    _eventHandler(t, e) {
      const i = {
        event: t,
        replay: e,
        cancelable: !0,
        inChartArea: this.isPointInArea(t),
      };
      const s = e =>
        (e.options.events || this.options.events).includes(t.native.type);
      if (!1 === this.notifyPlugins('beforeEvent', i, s)) {
        return;
      }
      const n = this._handleEvent(t, e, i.inChartArea);
      return (
        (i.cancelable = !1),
        this.notifyPlugins('afterEvent', i, s),
        (n || i.changed) && this.render(),
        this
      );
    }

    _handleEvent(t, e, i) {
      const { _active: s = [], options: n } = this;
      const o = e;
      const a = this._getActiveElements(t, s, i, o);
      const r = D(t);
      const l = (function(t, e, i, s) {
        return i && t.type !== 'mouseout' ? (s ? e : t) : null;
      })(t, this._lastEvent, i, r);
      i &&
        ((this._lastEvent = null),
        d(n.onHover, [t, a, this], this),
        r && d(n.onClick, [t, a, this], this));
      const h = !f(a, s);
      return (
        (h || e) && ((this._active = a), this._updateHoverStyles(a, s, e)),
        (this._lastEvent = l),
        h
      );
    }

    _getActiveElements(t, e, i, s) {
      if (t.type === 'mouseout') {
        return [];
      }
      if (!i) {
        return e;
      }
      const n = this.options.hover;
      return this.getElementsAtEventForMode(t, n.mode, n, s);
    }
  }
  function Ln() {
    return u(Tn.instances, t => t._plugins.invalidate());
  }
  function En() {
    throw new Error(
      'This method is not implemented: Check that a complete date adapter is provided.',
    );
  }
  class Rn {
    static override(t) {
      Object.assign(Rn.prototype, t);
    }

    options;
    constructor(t) {
      this.options = t || {};
    }

    init() {}
    formats() {
      return En();
    }

    parse() {
      return En();
    }

    format() {
      return En();
    }

    add() {
      return En();
    }

    diff() {
      return En();
    }

    startOf() {
      return En();
    }

    endOf() {
      return En();
    }
  }
  const In = { _date: Rn };
  function zn(t) {
    const e = t.iScale;
    const i = (function(t, e) {
      if (!t._cache.$bar) {
        const i = t.getMatchingVisibleMetas(e);
        let s = [];
        for (let e = 0, n = i.length; e < n; e++) {
          s = s.concat(i[e].controller.getAllParsedValues(t));
        }
        t._cache.$bar = lt(s.sort((t, e) => t - e));
      }
      return t._cache.$bar;
    })(e, t.type);
    let s;
    let n;
    let o;
    let a;
    let r = e._length;
    const l = () => {
      o !== 32767 &&
        o !== -32768 &&
        (k(a) && (r = Math.min(r, Math.abs(o - a) || r)), (a = o));
    };
    for (s = 0, n = i.length; s < n; ++s) {
      ((o = e.getPixelForValue(i[s])), l());
    }
    for (a = void 0, s = 0, n = e.ticks.length; s < n; ++s) {
      ((o = e.getPixelForTick(s)), l());
    }
    return r;
  }
  function Fn(t, e, i, s) {
    return (
      n(t) ?
        (function(t, e, i, s) {
          const n = i.parse(t[0], s);
          const o = i.parse(t[1], s);
          const a = Math.min(n, o);
          const r = Math.max(n, o);
          let l = a;
          let h = r;
          (Math.abs(a) > Math.abs(r) && ((l = r), (h = a)),
          (e[i.axis] = h),
          (e._custom = {
            barStart: l,
            barEnd: h,
            start: n,
            end: o,
            min: a,
            max: r,
          }));
        })(t, e, i, s) :
        (e[i.axis] = i.parse(t, s)),
      e
    );
  }
  function Vn(t, e, i, s) {
    const n = t.iScale;
    const o = t.vScale;
    const a = n.getLabels();
    const r = n === o;
    const l = [];
    let h, c, d, u;
    for (h = i, c = i + s; h < c; ++h) {
      ((u = e[h]),
      (d = {}),
      (d[n.axis] = r || n.parse(a[h], h)),
      l.push(Fn(u, d, o, h)));
    }
    return l;
  }
  function Bn(t) {
    return t && void 0 !== t.barStart && void 0 !== t.barEnd;
  }
  function Wn(t, e, i, s) {
    let n = e.borderSkipped;
    const o = {};
    if (!n) {
      return void (t.borderSkipped = o);
    }
    if (!0 === n) {
      return void (t.borderSkipped = {
        top: !0,
        right: !0,
        bottom: !0,
        left: !0,
      });
    }
    const {
      start: a,
      end: r,
      reverse: l,
      top: h,
      bottom: c,
    } = (function(t) {
      let e, i, s, n, o;
      return (
        t.horizontal ?
          ((e = t.base > t.x), (i = 'left'), (s = 'right')) :
          ((e = t.base < t.y), (i = 'bottom'), (s = 'top')),
        e ? ((n = 'end'), (o = 'start')) : ((n = 'start'), (o = 'end')),
        { start: i, end: s, reverse: e, top: n, bottom: o }
      );
    })(t);
    (n === 'middle' &&
      i &&
      ((t.enableBorderRadius = !0),
      (i._top || 0) === s ?
        (n = h) :
        (i._bottom || 0) === s ?
          (n = c) :
          ((o[Nn(c, a, r, l)] = !0), (n = h))),
    (o[Nn(n, a, r, l)] = !0),
    (t.borderSkipped = o));
  }
  function Nn(t, e, i, s) {
    let n, o, a;
    return (
      s ?
        ((a = i),
        (t = Hn((t = (n = t) === (o = e) ? a : n === a ? o : n), i, e))) :
        (t = Hn(t, e, i)),
      t
    );
  }
  function Hn(t, e, i) {
    return t === 'start' ? e : t === 'end' ? i : t;
  }
  function jn(t, { inflateAmount: e }, i) {
    t.inflateAmount = e === 'auto' ? (i === 1 ? 0.33 : 0) : e;
  }
  class $n extends js {
    static id = 'doughnut';
    static defaults = {
      datasetElementType: !1,
      dataElementType: 'arc',
      animation: { animateRotate: !0, animateScale: !1 },
      animations: {
        numbers: {
          type: 'number',
          properties: [
            'circumference',
            'endAngle',
            'innerRadius',
            'outerRadius',
            'startAngle',
            'x',
            'y',
            'offset',
            'borderWidth',
            'spacing',
          ],
        },
      },
      cutout: '50%',
      rotation: 0,
      circumference: 360,
      radius: '100%',
      spacing: 0,
      indexAxis: 'r',
    };

    static descriptors = {
      _scriptable: t => t !== 'spacing',
      _indexable: t =>
        t !== 'spacing' &&
        !t.startsWith('borderDash') &&
        !t.startsWith('hoverBorderDash'),
    };

    static overrides = {
      aspectRatio: 1,
      plugins: {
        legend: {
          labels: {
            generateLabels(t) {
              const e = t.data;
              if (e.labels.length && e.datasets.length) {
                const {
                  labels: { pointStyle: i, color: s },
                } = t.legend.options;
                return e.labels.map((e, n) => {
                  const o = t.getDatasetMeta(0).controller.getStyle(n);
                  return {
                    text: e,
                    fillStyle: o.backgroundColor,
                    strokeStyle: o.borderColor,
                    fontColor: s,
                    lineWidth: o.borderWidth,
                    pointStyle: i,
                    hidden: !t.getDataVisibility(n),
                    index: n,
                  };
                });
              }
              return [];
            },
          },
          onClick(t, e, i) {
            (i.chart.toggleDataVisibility(e.index), i.chart.update());
          },
        },
      },
    };

    constructor(t, e) {
      (super(t, e),
      (this.enableOptionSharing = !0),
      (this.innerRadius = void 0),
      (this.outerRadius = void 0),
      (this.offsetX = void 0),
      (this.offsetY = void 0));
    }

    linkScales() {}
    parse(t, e) {
      const i = this.getDataset().data;
      const s = this._cachedMeta;
      if (!1 === this._parsing) {
        s._parsed = i;
      } else {
        let n;
        let a;
        let r = t => +i[t];
        if (o(i[t])) {
          const { key: t = 'value' } = this._parsing;
          r = e => +M(i[e], t);
        }
        for (n = t, a = t + e; n < a; ++n) {
          s._parsed[n] = r(n);
        }
      }
    }

    _getRotation() {
      return $(this.options.rotation - 90);
    }

    _getCircumference() {
      return $(this.options.circumference);
    }

    _getRotationExtents() {
      let t = O;
      let e = -O;
      for (let i = 0; i < this.chart.data.datasets.length; ++i) {
        if (
          this.chart.isDatasetVisible(i) &&
          this.chart.getDatasetMeta(i).type === this._type
        ) {
          const s = this.chart.getDatasetMeta(i).controller;
          const n = s._getRotation();
          const o = s._getCircumference();
          ((t = Math.min(t, n)), (e = Math.max(e, n + o)));
        }
      }
      return { rotation: t, circumference: e - t };
    }

    update(t) {
      const e = this.chart;
      const { chartArea: i } = e;
      const s = this._cachedMeta;
      const n = s.data;
      const o =
          this.getMaxBorderWidth() +
          this.getMaxOffset(n) +
          this.options.spacing;
      const a = Math.max((Math.min(i.width, i.height) - o) / 2, 0);
      const r = Math.min(h(this.options.cutout, a), 1);
      const l = this._getRingWeight(this.index);
      const { circumference: d, rotation: u } = this._getRotationExtents();
      const {
        ratioX: f,
        ratioY: g,
        offsetX: p,
        offsetY: m,
      } = (function(t, e, i) {
        let s = 1;
        let n = 1;
        let o = 0;
        let a = 0;
        if (e < O) {
          const r = t;
          const l = r + e;
          const h = Math.cos(r);
          const c = Math.sin(r);
          const d = Math.cos(l);
          const u = Math.sin(l);
          const f = (t, e, s) =>
            J(t, r, l, !0) ? 1 : Math.max(e, e * i, s, s * i);
          const g = (t, e, s) =>
            J(t, r, l, !0) ? -1 : Math.min(e, e * i, s, s * i);
          const p = f(0, h, d);
          const m = f(E, c, u);
          const x = g(C, h, d);
          const b = g(C + E, c, u);
          ((s = (p - x) / 2),
          (n = (m - b) / 2),
          (o = -(p + x) / 2),
          (a = -(m + b) / 2));
        }
        return { ratioX: s, ratioY: n, offsetX: o, offsetY: a };
      })(u, d, r);
      const x = (i.width - o) / f;
      const b = (i.height - o) / g;
      const _ = Math.max(Math.min(x, b) / 2, 0);
      const y = c(this.options.radius, _);
      const v = (y - Math.max(y * r, 0)) / this._getVisibleDatasetWeightTotal();
      ((this.offsetX = p * y),
      (this.offsetY = m * y),
      (s.total = this.calculateTotal()),
      (this.outerRadius = y - v * this._getRingWeightOffset(this.index)),
      (this.innerRadius = Math.max(this.outerRadius - v * l, 0)),
      this.updateElements(n, 0, n.length, t));
    }

    _circumference(t, e) {
      const i = this.options;
      const s = this._cachedMeta;
      const n = this._getCircumference();
      return (e && i.animation.animateRotate) ||
        !this.chart.getDataVisibility(t) ||
        s._parsed[t] === null ||
        s.data[t].hidden ?
        0 :
        this.calculateCircumference((s._parsed[t] * n) / O);
    }

    updateElements(t, e, i, s) {
      const n = s === 'reset';
      const o = this.chart;
      const a = o.chartArea;
      const r = o.options.animation;
      const l = (a.left + a.right) / 2;
      const h = (a.top + a.bottom) / 2;
      const c = n && r.animateScale;
      const d = c ? 0 : this.innerRadius;
      const u = c ? 0 : this.outerRadius;
      const { sharedOptions: f, includeOptions: g } = this._getSharedOptions(e, s);
      let p;
      let m = this._getRotation();
      for (p = 0; p < e; ++p) {
        m += this._circumference(p, n);
      }
      for (p = e; p < e + i; ++p) {
        const e = this._circumference(p, n);
        const i = t[p];
        const o = {
          x: l + this.offsetX,
          y: h + this.offsetY,
          startAngle: m,
          endAngle: m + e,
          circumference: e,
          outerRadius: u,
          innerRadius: d,
        };
        (g &&
          (o.options =
            f || this.resolveDataElementOptions(p, i.active ? 'active' : s)),
        (m += e),
        this.updateElement(i, p, o, s));
      }
    }

    calculateTotal() {
      const t = this._cachedMeta;
      const e = t.data;
      let i;
      let s = 0;
      for (i = 0; i < e.length; i++) {
        const n = t._parsed[i];
        n === null ||
          isNaN(n) ||
          !this.chart.getDataVisibility(i) ||
          e[i].hidden ||
          (s += Math.abs(n));
      }
      return s;
    }

    calculateCircumference(t) {
      const e = this._cachedMeta.total;
      return e > 0 && !isNaN(t) ? O * (Math.abs(t) / e) : 0;
    }

    getLabelAndValue(t) {
      const e = this._cachedMeta;
      const i = this.chart;
      const s = i.data.labels || [];
      const n = ne(e._parsed[t], i.options.locale);
      return { label: s[t] || '', value: n };
    }

    getMaxBorderWidth(t) {
      let e = 0;
      const i = this.chart;
      let s, n, o, a, r;
      if (!t) {
        for (s = 0, n = i.data.datasets.length; s < n; ++s) {
          if (i.isDatasetVisible(s)) {
            ((o = i.getDatasetMeta(s)), (t = o.data), (a = o.controller));
            break;
          }
        }
      }
      if (!t) {
        return 0;
      }
      for (s = 0, n = t.length; s < n; ++s) {
        ((r = a.resolveDataElementOptions(s)),
        r.borderAlign !== 'inner' &&
            (e = Math.max(e, r.borderWidth || 0, r.hoverBorderWidth || 0)));
      }
      return e;
    }

    getMaxOffset(t) {
      let e = 0;
      for (let i = 0, s = t.length; i < s; ++i) {
        const t = this.resolveDataElementOptions(i);
        e = Math.max(e, t.offset || 0, t.hoverOffset || 0);
      }
      return e;
    }

    _getRingWeightOffset(t) {
      let e = 0;
      for (let i = 0; i < t; ++i) {
        this.chart.isDatasetVisible(i) && (e += this._getRingWeight(i));
      }
      return e;
    }

    _getRingWeight(t) {
      return Math.max(l(this.chart.data.datasets[t].weight, 1), 0);
    }

    _getVisibleDatasetWeightTotal() {
      return this._getRingWeightOffset(this.chart.data.datasets.length) || 1;
    }
  }
  class Yn extends js {
    static id = 'polarArea';
    static defaults = {
      dataElementType: 'arc',
      animation: { animateRotate: !0, animateScale: !0 },
      animations: {
        numbers: {
          type: 'number',
          properties: [
            'x',
            'y',
            'startAngle',
            'endAngle',
            'innerRadius',
            'outerRadius',
          ],
        },
      },
      indexAxis: 'r',
      startAngle: 0,
    };

    static overrides = {
      aspectRatio: 1,
      plugins: {
        legend: {
          labels: {
            generateLabels(t) {
              const e = t.data;
              if (e.labels.length && e.datasets.length) {
                const {
                  labels: { pointStyle: i, color: s },
                } = t.legend.options;
                return e.labels.map((e, n) => {
                  const o = t.getDatasetMeta(0).controller.getStyle(n);
                  return {
                    text: e,
                    fillStyle: o.backgroundColor,
                    strokeStyle: o.borderColor,
                    fontColor: s,
                    lineWidth: o.borderWidth,
                    pointStyle: i,
                    hidden: !t.getDataVisibility(n),
                    index: n,
                  };
                });
              }
              return [];
            },
          },
          onClick(t, e, i) {
            (i.chart.toggleDataVisibility(e.index), i.chart.update());
          },
        },
      },
      scales: {
        r: {
          type: 'radialLinear',
          angleLines: { display: !1 },
          beginAtZero: !0,
          grid: { circular: !0 },
          pointLabels: { display: !1 },
          startAngle: 0,
        },
      },
    };

    constructor(t, e) {
      (super(t, e), (this.innerRadius = void 0), (this.outerRadius = void 0));
    }

    getLabelAndValue(t) {
      const e = this._cachedMeta;
      const i = this.chart;
      const s = i.data.labels || [];
      const n = ne(e._parsed[t].r, i.options.locale);
      return { label: s[t] || '', value: n };
    }

    parseObjectData(t, e, i, s) {
      return ii.bind(this)(t, e, i, s);
    }

    update(t) {
      const e = this._cachedMeta.data;
      (this._updateRadius(), this.updateElements(e, 0, e.length, t));
    }

    getMinMax() {
      const t = this._cachedMeta;
      const e = { min: Number.POSITIVE_INFINITY, max: Number.NEGATIVE_INFINITY };
      return (
        t.data.forEach((t, i) => {
          const s = this.getParsed(i).r;
          !isNaN(s) &&
            this.chart.getDataVisibility(i) &&
            (s < e.min && (e.min = s), s > e.max && (e.max = s));
        }),
        e
      );
    }

    _updateRadius() {
      const t = this.chart;
      const e = t.chartArea;
      const i = t.options;
      const s = Math.min(e.right - e.left, e.bottom - e.top);
      const n = Math.max(s / 2, 0);
      const o =
          (n -
            Math.max(
              i.cutoutPercentage ? (n / 100) * i.cutoutPercentage : 1,
              0,
            )) /
          t.getVisibleDatasetCount();
      ((this.outerRadius = n - o * this.index),
      (this.innerRadius = this.outerRadius - o));
    }

    updateElements(t, e, i, s) {
      const n = s === 'reset';
      const o = this.chart;
      const a = o.options.animation;
      const r = this._cachedMeta.rScale;
      const l = r.xCenter;
      const h = r.yCenter;
      const c = r.getIndexAngle(0) - 0.5 * C;
      let d;
      let u = c;
      const f = 360 / this.countVisibleElements();
      for (d = 0; d < e; ++d) {
        u += this._computeAngle(d, s, f);
      }
      for (d = e; d < e + i; d++) {
        const e = t[d];
        let i = u;
        let g = u + this._computeAngle(d, s, f);
        let p = o.getDataVisibility(d) ?
          r.getDistanceFromCenterForValue(this.getParsed(d).r) :
          0;
        ((u = g),
        n && (a.animateScale && (p = 0), a.animateRotate && (i = g = c)));
        const m = {
          x: l,
          y: h,
          innerRadius: 0,
          outerRadius: p,
          startAngle: i,
          endAngle: g,
          options: this.resolveDataElementOptions(d, e.active ? 'active' : s),
        };
        this.updateElement(e, d, m, s);
      }
    }

    countVisibleElements() {
      const t = this._cachedMeta;
      let e = 0;
      return (
        t.data.forEach((t, i) => {
          !isNaN(this.getParsed(i).r) && this.chart.getDataVisibility(i) && e++;
        }),
        e
      );
    }

    _computeAngle(t, e, i) {
      return this.chart.getDataVisibility(t) ?
        $(this.resolveDataElementOptions(t, e).angle || i) :
        0;
    }
  }
  const Un = Object.freeze({
    __proto__: null,
    BarController: class extends js {
      static id = 'bar';
      static defaults = {
        datasetElementType: !1,
        dataElementType: 'bar',
        categoryPercentage: 0.8,
        barPercentage: 0.9,
        grouped: !0,
        animations: {
          numbers: {
            type: 'number',
            properties: ['x', 'y', 'base', 'width', 'height'],
          },
        },
      };

      static overrides = {
        scales: {
          _index_: { type: 'category', offset: !0, grid: { offset: !0 } },
          _value_: { type: 'linear', beginAtZero: !0 },
        },
      };

      parsePrimitiveData(t, e, i, s) {
        return Vn(t, e, i, s);
      }

      parseArrayData(t, e, i, s) {
        return Vn(t, e, i, s);
      }

      parseObjectData(t, e, i, s) {
        const { iScale: n, vScale: o } = t;
        const { xAxisKey: a = 'x', yAxisKey: r = 'y' } = this._parsing;
        const l = n.axis === 'x' ? a : r;
        const h = o.axis === 'x' ? a : r;
        const c = [];
        let d, u, f, g;
        for (d = i, u = i + s; d < u; ++d) {
          ((g = e[d]),
          (f = {}),
          (f[n.axis] = n.parse(M(g, l), d)),
          c.push(Fn(M(g, h), f, o, d)));
        }
        return c;
      }

      updateRangeFromParsed(t, e, i, s) {
        super.updateRangeFromParsed(t, e, i, s);
        const n = i._custom;
        n &&
          e === this._cachedMeta.vScale &&
          ((t.min = Math.min(t.min, n.min)), (t.max = Math.max(t.max, n.max)));
      }

      getMaxOverflow() {
        return 0;
      }

      getLabelAndValue(t) {
        const e = this._cachedMeta;
        const { iScale: i, vScale: s } = e;
        const n = this.getParsed(t);
        const o = n._custom;
        const a = Bn(o) ?
          `[${o.start}, ${o.end}]` :
          `${s.getLabelForValue(n[s.axis])}`;
        return { label: `${i.getLabelForValue(n[i.axis])}`, value: a };
      }

      initialize() {
        ((this.enableOptionSharing = !0), super.initialize());
        this._cachedMeta.stack = this.getDataset().stack;
      }

      update(t) {
        const e = this._cachedMeta;
        this.updateElements(e.data, 0, e.data.length, t);
      }

      updateElements(t, e, i, n) {
        const o = n === 'reset';
        const {
          index: a,
          _cachedMeta: { vScale: r },
        } = this;
        const l = r.getBasePixel();
        const h = r.isHorizontal();
        const c = this._getRuler();
        const { sharedOptions: d, includeOptions: u } = this._getSharedOptions(
          e,
          n,
        );
        for (let f = e; f < e + i; f++) {
          const e = this.getParsed(f);
          const i =
              o || s(e[r.axis]) ?
                { base: l, head: l } :
                this._calculateBarValuePixels(f);
          const g = this._calculateBarIndexPixels(f, c);
          const p = (e._stacks || {})[r.axis];
          const m = {
            horizontal: h,
            base: i.base,
            enableBorderRadius:
                !p || Bn(e._custom) || a === p._top || a === p._bottom,
            x: h ? i.head : g.center,
            y: h ? g.center : i.head,
            height: h ? g.size : Math.abs(i.size),
            width: h ? Math.abs(i.size) : g.size,
          };
          u &&
            (m.options =
              d ||
              this.resolveDataElementOptions(f, t[f].active ? 'active' : n));
          const x = m.options || t[f].options;
          (Wn(m, x, p, a),
          jn(m, x, c.ratio),
          this.updateElement(t[f], f, m, n));
        }
      }

      _getStacks(t, e) {
        const { iScale: i } = this._cachedMeta;
        const n = i
          .getMatchingVisibleMetas(this._type)
          .filter(t => t.controller.options.grouped);
        const o = i.options.stacked;
        const a = [];
        const r = this._cachedMeta.controller.getParsed(e);
        const l = r && r[i.axis];
        const h = t => {
          const e = t._parsed.find(t => t[i.axis] === l);
          const n = e && e[t.vScale.axis];
          if (s(n) || isNaN(n)) {
            return !0;
          }
        };
        for (const i of n) {
          if (
            (void 0 === e || !h(i)) &&
            ((!1 === o ||
              a.indexOf(i.stack) === -1 ||
              (void 0 === o && void 0 === i.stack)) &&
              a.push(i.stack),
            i.index === t)
          ) {
            break;
          }
        }
        return (a.length || a.push(void 0), a);
      }

      _getStackCount(t) {
        return this._getStacks(void 0, t).length;
      }

      _getAxisCount() {
        return this._getAxis().length;
      }

      getFirstScaleIdForIndexAxis() {
        const t = this.chart.scales;
        const e = this.chart.options.indexAxis;
        return Object.keys(t)
          .filter(i => t[i].axis === e)
          .shift();
      }

      _getAxis() {
        const t = {};
        const e = this.getFirstScaleIdForIndexAxis();
        for (const i of this.chart.data.datasets) {
          t[
            l(this.chart.options.indexAxis === 'x' ? i.xAxisID : i.yAxisID, e)
          ] = !0;
        }
        return Object.keys(t);
      }

      _getStackIndex(t, e, i) {
        const s = this._getStacks(t, i);
        const n = void 0 !== e ? s.indexOf(e) : -1;
        return n === -1 ? s.length - 1 : n;
      }

      _getRuler() {
        const t = this.options;
        const e = this._cachedMeta;
        const i = e.iScale;
        const s = [];
        let n, o;
        for (n = 0, o = e.data.length; n < o; ++n) {
          s.push(i.getPixelForValue(this.getParsed(n)[i.axis], n));
        }
        const a = t.barThickness;
        return {
          min: a || zn(e),
          pixels: s,
          start: i._startPixel,
          end: i._endPixel,
          stackCount: this._getStackCount(),
          scale: i,
          grouped: t.grouped,
          ratio: a ? 1 : t.categoryPercentage * t.barPercentage,
        };
      }

      _calculateBarValuePixels(t) {
        const {
          _cachedMeta: { vScale: e, _stacked: i, index: n },
          options: { base: o, minBarLength: a },
        } = this;
        const r = o || 0;
        const l = this.getParsed(t);
        const h = l._custom;
        const c = Bn(h);
        let d;
        let u;
        let f = l[e.axis];
        let g = 0;
        let p = i ? this.applyStack(e, l, i) : f;
        (p !== f && ((g = p - f), (p = f)),
        c &&
            ((f = h.barStart),
            (p = h.barEnd - h.barStart),
            f !== 0 && F(f) !== F(h.barEnd) && (g = 0),
            (g += f)));
        const m = s(o) || c ? g : o;
        let x = e.getPixelForValue(m);
        if (
          ((d = this.chart.getDataVisibility(t) ?
            e.getPixelForValue(g + p) :
            x),
          (u = d - x),
          Math.abs(u) < a)
        ) {
          ((u =
            (function(t, e, i) {
              return t !== 0 ?
                F(t) :
                (e.isHorizontal() ? 1 : -1) * (e.min >= i ? 1 : -1);
            })(u, e, r) * a),
          f === r && (x -= u / 2));
          const t = e.getPixelForDecimal(0);
          const s = e.getPixelForDecimal(1);
          const o = Math.min(t, s);
          const h = Math.max(t, s);
          ((x = Math.max(Math.min(x, h), o)),
          (d = x + u),
          i &&
              !c &&
              (l._stacks[e.axis]._visualValues[n] =
                e.getValueForPixel(d) - e.getValueForPixel(x)));
        }
        if (x === e.getPixelForValue(r)) {
          const t = (F(u) * e.getLineWidthForValue(r)) / 2;
          ((x += t), (u -= t));
        }
        return { size: u, base: x, head: d, center: d + u / 2 };
      }

      _calculateBarIndexPixels(t, e) {
        const i = e.scale;
        const n = this.options;
        const o = n.skipNull;
        const a = l(n.maxBarThickness, 1 / 0);
        let r, h;
        const c = this._getAxisCount();
        if (e.grouped) {
          const i = o ? this._getStackCount(t) : e.stackCount;
          const d =
              n.barThickness === 'flex' ?
                (function(t, e, i, s) {
                  const n = e.pixels;
                  const o = n[t];
                  let a = t > 0 ? n[t - 1] : null;
                  let r = t < n.length - 1 ? n[t + 1] : null;
                  const l = i.categoryPercentage;
                  (a === null &&
                      (a = o - (r === null ? e.end - e.start : r - o)),
                  r === null && (r = o + o - a));
                  const h = o - ((o - Math.min(a, r)) / 2) * l;
                  return {
                    chunk: ((Math.abs(r - a) / 2) * l) / s,
                    ratio: i.barPercentage,
                    start: h,
                  };
                })(t, e, n, i * c) :
                (function(t, e, i, n) {
                  const o = i.barThickness;
                  let a, r;
                  return (
                    s(o) ?
                      ((a = e.min * i.categoryPercentage),
                      (r = i.barPercentage)) :
                      ((a = o * n), (r = 1)),
                    { chunk: a / n, ratio: r, start: e.pixels[t] - a / 2 }
                  );
                })(t, e, n, i * c);
          const u =
              this.chart.options.indexAxis === 'x' ?
                this.getDataset().xAxisID :
                this.getDataset().yAxisID;
          const f = this._getAxis().indexOf(
            l(u, this.getFirstScaleIdForIndexAxis()),
          );
          const g =
              this._getStackIndex(
                this.index,
                this._cachedMeta.stack,
                o ? t : void 0,
              ) + f;
          ((r = d.start + d.chunk * g + d.chunk / 2),
          (h = Math.min(a, d.chunk * d.ratio)));
        } else {
          ((r = i.getPixelForValue(this.getParsed(t)[i.axis], t)),
          (h = Math.min(a, e.min * e.ratio)));
        }
        return { base: r - h / 2, head: r + h / 2, center: r, size: h };
      }

      draw() {
        const t = this._cachedMeta;
        const e = t.vScale;
        const i = t.data;
        const s = i.length;
        let n = 0;
        for (; n < s; ++n) {
          this.getParsed(n)[e.axis] === null ||
            i[n].hidden ||
            i[n].draw(this._ctx);
        }
      }
    },
    BubbleController: class extends js {
      static id = 'bubble';
      static defaults = {
        datasetElementType: !1,
        dataElementType: 'point',
        animations: {
          numbers: {
            type: 'number',
            properties: ['x', 'y', 'borderWidth', 'radius'],
          },
        },
      };

      static overrides = {
        scales: { x: { type: 'linear' }, y: { type: 'linear' } },
      };

      initialize() {
        ((this.enableOptionSharing = !0), super.initialize());
      }

      parsePrimitiveData(t, e, i, s) {
        const n = super.parsePrimitiveData(t, e, i, s);
        for (let t = 0; t < n.length; t++) {
          n[t]._custom = this.resolveDataElementOptions(t + i).radius;
        }
        return n;
      }

      parseArrayData(t, e, i, s) {
        const n = super.parseArrayData(t, e, i, s);
        for (let t = 0; t < n.length; t++) {
          const s = e[i + t];
          n[t]._custom = l(s[2], this.resolveDataElementOptions(t + i).radius);
        }
        return n;
      }

      parseObjectData(t, e, i, s) {
        const n = super.parseObjectData(t, e, i, s);
        for (let t = 0; t < n.length; t++) {
          const s = e[i + t];
          n[t]._custom = l(
            s && s.r && +s.r,
            this.resolveDataElementOptions(t + i).radius,
          );
        }
        return n;
      }

      getMaxOverflow() {
        const t = this._cachedMeta.data;
        let e = 0;
        for (let i = t.length - 1; i >= 0; --i) {
          e = Math.max(e, t[i].size(this.resolveDataElementOptions(i)) / 2);
        }
        return e > 0 && e;
      }

      getLabelAndValue(t) {
        const e = this._cachedMeta;
        const i = this.chart.data.labels || [];
        const { xScale: s, yScale: n } = e;
        const o = this.getParsed(t);
        const a = s.getLabelForValue(o.x);
        const r = n.getLabelForValue(o.y);
        const l = o._custom;
        return {
          label: i[t] || '',
          value: `(${a}, ${r}${l ? `, ${l}` : ''})`,
        };
      }

      update(t) {
        const e = this._cachedMeta.data;
        this.updateElements(e, 0, e.length, t);
      }

      updateElements(t, e, i, s) {
        const n = s === 'reset';
        const { iScale: o, vScale: a } = this._cachedMeta;
        const { sharedOptions: r, includeOptions: l } = this._getSharedOptions(
          e,
          s,
        );
        const h = o.axis;
        const c = a.axis;
        for (let d = e; d < e + i; d++) {
          const e = t[d];
          const i = !n && this.getParsed(d);
          const u = {};
          const f = (u[h] = n ?
            o.getPixelForDecimal(0.5) :
            o.getPixelForValue(i[h]));
          const g = (u[c] = n ? a.getBasePixel() : a.getPixelForValue(i[c]));
          ((u.skip = isNaN(f) || isNaN(g)),
          l &&
              ((u.options =
                r ||
                this.resolveDataElementOptions(d, e.active ? 'active' : s)),
              n && (u.options.radius = 0)),
          this.updateElement(e, d, u, s));
        }
      }

      resolveDataElementOptions(t, e) {
        const i = this.getParsed(t);
        let s = super.resolveDataElementOptions(t, e);
        s.$shared && (s = Object.assign({}, s, { $shared: !1 }));
        const n = s.radius;
        return (
          e !== 'active' && (s.radius = 0),
          (s.radius += l(i && i._custom, n)),
          s
        );
      }
    },
    DoughnutController: $n,
    LineController: class extends js {
      static id = 'line';
      static defaults = {
        datasetElementType: 'line',
        dataElementType: 'point',
        showLine: !0,
        spanGaps: !1,
      };

      static overrides = {
        scales: { _index_: { type: 'category' }, _value_: { type: 'linear' } },
      };

      initialize() {
        ((this.enableOptionSharing = !0),
        (this.supportsDecimation = !0),
        super.initialize());
      }

      update(t) {
        const e = this._cachedMeta;
        const { dataset: i, data: s = [], _dataset: n } = e;
        const o = this.chart._animationsDisabled;
        let { start: a, count: r } = pt(e, s, o);
        ((this._drawStart = a),
        (this._drawCount = r),
        mt(e) && ((a = 0), (r = s.length)),
        (i._chart = this.chart),
        (i._datasetIndex = this.index),
        (i._decimated = !!n._decimated),
        (i.points = s));
        const l = this.resolveDatasetElementOptions(t);
        (this.options.showLine || (l.borderWidth = 0),
        (l.segment = this.options.segment),
        this.updateElement(i, void 0, { animated: !o, options: l }, t),
        this.updateElements(s, a, r, t));
      }

      updateElements(t, e, i, n) {
        const o = n === 'reset';
        const { iScale: a, vScale: r, _stacked: l, _dataset: h } = this._cachedMeta;
        const { sharedOptions: c, includeOptions: d } = this._getSharedOptions(
          e,
          n,
        );
        const u = a.axis;
        const f = r.axis;
        const { spanGaps: g, segment: p } = this.options;
        const m = N(g) ? g : Number.POSITIVE_INFINITY;
        const x = this.chart._animationsDisabled || o || n === 'none';
        const b = e + i;
        const _ = t.length;
        let y = e > 0 && this.getParsed(e - 1);
        for (let i = 0; i < _; ++i) {
          const g = t[i];
          const _ = x ? g : {};
          if (i < e || i >= b) {
            _.skip = !0;
            continue;
          }
          const v = this.getParsed(i);
          const M = s(v[f]);
          const w = (_[u] = a.getPixelForValue(v[u], i));
          const k = (_[f] =
              o || M ?
                r.getBasePixel() :
                r.getPixelForValue(l ? this.applyStack(r, v, l) : v[f], i));
          ((_.skip = isNaN(w) || isNaN(k) || M),
          (_.stop = i > 0 && Math.abs(v[u] - y[u]) > m),
          p && ((_.parsed = v), (_.raw = h.data[i])),
          d &&
              (_.options =
                c ||
                this.resolveDataElementOptions(i, g.active ? 'active' : n)),
          x || this.updateElement(g, i, _, n),
          (y = v));
        }
      }

      getMaxOverflow() {
        const t = this._cachedMeta;
        const e = t.dataset;
        const i = (e.options && e.options.borderWidth) || 0;
        const s = t.data || [];
        if (!s.length) {
          return i;
        }
        const n = s[0].size(this.resolveDataElementOptions(0));
        const o = s[s.length - 1].size(
          this.resolveDataElementOptions(s.length - 1),
        );
        return Math.max(i, n, o) / 2;
      }

      draw() {
        const t = this._cachedMeta;
        (t.dataset.updateControlPoints(this.chart.chartArea, t.iScale.axis),
        super.draw());
      }
    },
    PieController: class extends $n {
      static id = 'pie';
      static defaults = {
        cutout: 0,
        rotation: 0,
        circumference: 360,
        radius: '100%',
      };
    },
    PolarAreaController: Yn,
    RadarController: class extends js {
      static id = 'radar';
      static defaults = {
        datasetElementType: 'line',
        dataElementType: 'point',
        indexAxis: 'r',
        showLine: !0,
        elements: { line: { fill: 'start' } },
      };

      static overrides = {
        aspectRatio: 1,
        scales: { r: { type: 'radialLinear' } },
      };

      getLabelAndValue(t) {
        const e = this._cachedMeta.vScale;
        const i = this.getParsed(t);
        return {
          label: e.getLabels()[t],
          value: `${e.getLabelForValue(i[e.axis])}`,
        };
      }

      parseObjectData(t, e, i, s) {
        return ii.bind(this)(t, e, i, s);
      }

      update(t) {
        const e = this._cachedMeta;
        const i = e.dataset;
        const s = e.data || [];
        const n = e.iScale.getLabels();
        if (((i.points = s), t !== 'resize')) {
          const e = this.resolveDatasetElementOptions(t);
          this.options.showLine || (e.borderWidth = 0);
          const o = { _loop: !0, _fullLoop: n.length === s.length, options: e };
          this.updateElement(i, void 0, o, t);
        }
        this.updateElements(s, 0, s.length, t);
      }

      updateElements(t, e, i, s) {
        const n = this._cachedMeta.rScale;
        const o = s === 'reset';
        for (let a = e; a < e + i; a++) {
          const e = t[a];
          const i = this.resolveDataElementOptions(a, e.active ? 'active' : s);
          const r = n.getPointPositionForValue(a, this.getParsed(a).r);
          const l = o ? n.xCenter : r.x;
          const h = o ? n.yCenter : r.y;
          const c = {
            x: l,
            y: h,
            angle: r.angle,
            skip: isNaN(l) || isNaN(h),
            options: i,
          };
          this.updateElement(e, a, c, s);
        }
      }
    },
    ScatterController: class extends js {
      static id = 'scatter';
      static defaults = {
        datasetElementType: !1,
        dataElementType: 'point',
        showLine: !1,
        fill: !1,
      };

      static overrides = {
        interaction: { mode: 'point' },
        scales: { x: { type: 'linear' }, y: { type: 'linear' } },
      };

      getLabelAndValue(t) {
        const e = this._cachedMeta;
        const i = this.chart.data.labels || [];
        const { xScale: s, yScale: n } = e;
        const o = this.getParsed(t);
        const a = s.getLabelForValue(o.x);
        const r = n.getLabelForValue(o.y);
        return { label: i[t] || '', value: `(${a}, ${r})` };
      }

      update(t) {
        const e = this._cachedMeta;
        const { data: i = [] } = e;
        const s = this.chart._animationsDisabled;
        let { start: n, count: o } = pt(e, i, s);
        if (
          ((this._drawStart = n),
          (this._drawCount = o),
          mt(e) && ((n = 0), (o = i.length)),
          this.options.showLine)
        ) {
          this.datasetElementType || this.addElements();
          const { dataset: n, _dataset: o } = e;
          ((n._chart = this.chart),
          (n._datasetIndex = this.index),
          (n._decimated = !!o._decimated),
          (n.points = i));
          const a = this.resolveDatasetElementOptions(t);
          ((a.segment = this.options.segment),
          this.updateElement(n, void 0, { animated: !s, options: a }, t));
        } else {
          this.datasetElementType &&
            (delete e.dataset, (this.datasetElementType = !1));
        }
        this.updateElements(i, n, o, t);
      }

      addElements() {
        const { showLine: t } = this.options;
        (!this.datasetElementType &&
          t &&
          (this.datasetElementType = this.chart.registry.getElement('line')),
        super.addElements());
      }

      updateElements(t, e, i, n) {
        const o = n === 'reset';
        const { iScale: a, vScale: r, _stacked: l, _dataset: h } = this._cachedMeta;
        const c = this.resolveDataElementOptions(e, n);
        const d = this.getSharedOptions(c);
        const u = this.includeOptions(n, d);
        const f = a.axis;
        const g = r.axis;
        const { spanGaps: p, segment: m } = this.options;
        const x = N(p) ? p : Number.POSITIVE_INFINITY;
        const b = this.chart._animationsDisabled || o || n === 'none';
        let _ = e > 0 && this.getParsed(e - 1);
        for (let c = e; c < e + i; ++c) {
          const e = t[c];
          const i = this.getParsed(c);
          const p = b ? e : {};
          const y = s(i[g]);
          const v = (p[f] = a.getPixelForValue(i[f], c));
          const M = (p[g] =
              o || y ?
                r.getBasePixel() :
                r.getPixelForValue(l ? this.applyStack(r, i, l) : i[g], c));
          ((p.skip = isNaN(v) || isNaN(M) || y),
          (p.stop = c > 0 && Math.abs(i[f] - _[f]) > x),
          m && ((p.parsed = i), (p.raw = h.data[c])),
          u &&
              (p.options =
                d ||
                this.resolveDataElementOptions(c, e.active ? 'active' : n)),
          b || this.updateElement(e, c, p, n),
          (_ = i));
        }
        this.updateSharedOptions(d, n, c);
      }

      getMaxOverflow() {
        const t = this._cachedMeta;
        const e = t.data || [];
        if (!this.options.showLine) {
          let t = 0;
          for (let i = e.length - 1; i >= 0; --i) {
            t = Math.max(t, e[i].size(this.resolveDataElementOptions(i)) / 2);
          }
          return t > 0 && t;
        }
        const i = t.dataset;
        const s = (i.options && i.options.borderWidth) || 0;
        if (!e.length) {
          return s;
        }
        const n = e[0].size(this.resolveDataElementOptions(0));
        const o = e[e.length - 1].size(
          this.resolveDataElementOptions(e.length - 1),
        );
        return Math.max(s, n, o) / 2;
      }
    },
  });
  function Xn(t, e, i, s) {
    const n = vi(t.options.borderRadius, [
      'outerStart',
      'outerEnd',
      'innerStart',
      'innerEnd',
    ]);
    const o = (i - e) / 2;
    const a = Math.min(o, (s * e) / 2);
    const r = t => {
      const e = ((i - Math.min(o, t)) * s) / 2;
      return Z(t, 0, Math.min(o, e));
    };
    return {
      outerStart: r(n.outerStart),
      outerEnd: r(n.outerEnd),
      innerStart: Z(n.innerStart, 0, a),
      innerEnd: Z(n.innerEnd, 0, a),
    };
  }
  function qn(t, e, i, s) {
    return { x: i + t * Math.cos(e), y: s + t * Math.sin(e) };
  }
  function Kn(t, e, i, s, n, o) {
    const { x: a, y: r, startAngle: l, pixelMargin: h, innerRadius: c } = e;
    const d = Math.max(e.outerRadius + s + i - h, 0);
    const u = c > 0 ? c + s + i + h : 0;
    let f = 0;
    const g = n - l;
    if (s) {
      const t = ((c > 0 ? c - s : 0) + (d > 0 ? d - s : 0)) / 2;
      f = (g - (t !== 0 ? (g * t) / (t + s) : g)) / 2;
    }
    const p = (g - Math.max(0.001, g * d - i / C) / d) / 2;
    const m = l + p + f;
    const x = n - p - f;
    const {
      outerStart: b,
      outerEnd: _,
      innerStart: y,
      innerEnd: v,
    } = Xn(e, u, d, x - m);
    const M = d - b;
    const w = d - _;
    const k = m + b / M;
    const S = x - _ / w;
    const P = u + y;
    const D = u + v;
    const O = m + y / P;
    const A = x - v / D;
    if ((t.beginPath(), o)) {
      const e = (k + S) / 2;
      if ((t.arc(a, r, d, k, e), t.arc(a, r, d, e, S), _ > 0)) {
        const e = qn(w, S, a, r);
        t.arc(e.x, e.y, _, S, x + E);
      }
      const i = qn(D, x, a, r);
      if ((t.lineTo(i.x, i.y), v > 0)) {
        const e = qn(D, A, a, r);
        t.arc(e.x, e.y, v, x + E, A + Math.PI);
      }
      const s = (x - v / u + (m + y / u)) / 2;
      if (
        (t.arc(a, r, u, x - v / u, s, !0),
        t.arc(a, r, u, s, m + y / u, !0),
        y > 0)
      ) {
        const e = qn(P, O, a, r);
        t.arc(e.x, e.y, y, O + Math.PI, m - E);
      }
      const n = qn(M, m, a, r);
      if ((t.lineTo(n.x, n.y), b > 0)) {
        const e = qn(M, k, a, r);
        t.arc(e.x, e.y, b, m - E, k);
      }
    } else {
      t.moveTo(a, r);
      const e = Math.cos(k) * d + a;
      const i = Math.sin(k) * d + r;
      t.lineTo(e, i);
      const s = Math.cos(S) * d + a;
      const n = Math.sin(S) * d + r;
      t.lineTo(s, n);
    }
    t.closePath();
  }
  function Gn(t, e, i, s, n) {
    const { fullCircles: o, startAngle: a, circumference: r, options: l } = e;
    const {
      borderWidth: h,
      borderJoinStyle: c,
      borderDash: d,
      borderDashOffset: u,
      borderRadius: f,
    } = l;
    const g = l.borderAlign === 'inner';
    if (!h) {
      return;
    }
    (t.setLineDash(d || []),
    (t.lineDashOffset = u),
    g ?
      ((t.lineWidth = 2 * h), (t.lineJoin = c || 'round')) :
      ((t.lineWidth = h), (t.lineJoin = c || 'bevel')));
    let p = e.endAngle;
    if (o) {
      Kn(t, e, i, s, p, n);
      for (let e = 0; e < o; ++e) {
        t.stroke();
      }
      isNaN(r) || (p = a + (r % O || O));
    }
    (g &&
      (function(t, e, i) {
        const {
          startAngle: s,
          pixelMargin: n,
          x: o,
          y: a,
          outerRadius: r,
          innerRadius: l,
        } = e;
        let h = n / r;
        (t.beginPath(),
        t.arc(o, a, r, s - h, i + h),
        l > n ?
          ((h = n / l), t.arc(o, a, l, i + h, s - h, !0)) :
          t.arc(o, a, n, i + E, s - E),
        t.closePath(),
        t.clip());
      })(t, e, p),
    l.selfJoin &&
        p - a >= C &&
        f === 0 &&
        c !== 'miter' &&
        (function(t, e, i) {
          const {
            startAngle: s,
            x: n,
            y: o,
            outerRadius: a,
            innerRadius: r,
            options: l,
          } = e;
          const { borderWidth: h, borderJoinStyle: c } = l;
          const d = Math.min(h / a, G(s - i));
          if (
            (t.beginPath(), t.arc(n, o, a - h / 2, s + d / 2, i - d / 2), r > 0)
          ) {
            const e = Math.min(h / r, G(s - i));
            t.arc(n, o, r + h / 2, i - e / 2, s + e / 2, !0);
          } else {
            const e = Math.min(h / 2, a * G(s - i));
            if (c === 'round') {
              t.arc(n, o, e, i - C / 2, s + C / 2, !0);
            } else if (c === 'bevel') {
              const a = 2 * e * e;
              const r = -a * Math.cos(i + C / 2) + n;
              const l = -a * Math.sin(i + C / 2) + o;
              const h = a * Math.cos(s + C / 2) + n;
              const c = a * Math.sin(s + C / 2) + o;
              (t.lineTo(r, l), t.lineTo(h, c));
            }
          }
          (t.closePath(),
          t.moveTo(0, 0),
          t.rect(0, 0, t.canvas.width, t.canvas.height),
          t.clip('evenodd'));
        })(t, e, p),
    o || (Kn(t, e, i, s, p, n), t.stroke()));
  }
  function Jn(t, e, i = e) {
    ((t.lineCap = l(i.borderCapStyle, e.borderCapStyle)),
    t.setLineDash(l(i.borderDash, e.borderDash)),
    (t.lineDashOffset = l(i.borderDashOffset, e.borderDashOffset)),
    (t.lineJoin = l(i.borderJoinStyle, e.borderJoinStyle)),
    (t.lineWidth = l(i.borderWidth, e.borderWidth)),
    (t.strokeStyle = l(i.borderColor, e.borderColor)));
  }
  function Zn(t, e, i) {
    t.lineTo(i.x, i.y);
  }
  function Qn(t, e, i = {}) {
    const s = t.length;
    const { start: n = 0, end: o = s - 1 } = i;
    const { start: a, end: r } = e;
    const l = Math.max(n, a);
    const h = Math.min(o, r);
    const c = (n < a && o < a) || (n > r && o > r);
    return {
      count: s,
      start: l,
      loop: e.loop,
      ilen: h < l && !c ? s + h - l : h - l,
    };
  }
  function to(t, e, i, s) {
    const { points: n, options: o } = e;
    const { count: a, start: r, loop: l, ilen: h } = Qn(n, i, s);
    const c = (function(t) {
      return t.stepped ?
        Fe :
        t.tension || t.cubicInterpolationMode === 'monotone' ?
          Ve :
          Zn;
    })(o);
    let d;
    let u;
    let f;
    let { move: g = !0, reverse: p } = s || {};
    for (d = 0; d <= h; ++d) {
      ((u = n[(r + (p ? h - d : d)) % a]),
      u.skip ||
          (g ? (t.moveTo(u.x, u.y), (g = !1)) : c(t, f, u, p, o.stepped),
          (f = u)));
    }
    return (
      l && ((u = n[(r + (p ? h : 0)) % a]), c(t, f, u, p, o.stepped)),
      !!l
    );
  }
  function eo(t, e, i, s) {
    const n = e.points;
    const { count: o, start: a, ilen: r } = Qn(n, i, s);
    const { move: l = !0, reverse: h } = s || {};
    let c;
    let d;
    let u;
    let f;
    let g;
    let p;
    let m = 0;
    let x = 0;
    const b = t => (a + (h ? r - t : t)) % o;
    const _ = () => {
      f !== g && (t.lineTo(m, g), t.lineTo(m, f), t.lineTo(m, p));
    };
    for (l && ((d = n[b(0)]), t.moveTo(d.x, d.y)), c = 0; c <= r; ++c) {
      if (((d = n[b(c)]), d.skip)) {
        continue;
      }
      const e = d.x;
      const i = d.y;
      const s = 0 | e;
      (s === u ?
        (i < f ? (f = i) : i > g && (g = i), (m = (x * m + e) / ++x)) :
        (_(), t.lineTo(e, i), (u = s), (x = 0), (f = g = i)),
      (p = i));
    }
    _();
  }
  function io(t) {
    const e = t.options;
    const i = e.borderDash && e.borderDash.length;
    return !(
      t._decimated ||
      t._loop ||
      e.tension ||
      e.cubicInterpolationMode === 'monotone' ||
      e.stepped ||
      i
    ) ?
      eo :
      to;
  }
  const so = typeof Path2D === 'function';
  function no(t, e, i, s) {
    so && !e.options.segment ?
      (function(t, e, i, s) {
        let n = e._path;
        (n ||
            ((n = e._path = new Path2D()), e.path(n, i, s) && n.closePath()),
        Jn(t, e.options),
        t.stroke(n));
      })(t, e, i, s) :
      (function(t, e, i, s) {
        const { segments: n, options: o } = e;
        const a = io(e);
        for (const r of n) {
          (Jn(t, o, r.style),
          t.beginPath(),
          a(t, e, r, { start: i, end: i + s - 1 }) && t.closePath(),
          t.stroke());
        }
      })(t, e, i, s);
  }
  class oo extends $s {
    static id = 'line';
    static defaults = {
      borderCapStyle: 'butt',
      borderDash: [],
      borderDashOffset: 0,
      borderJoinStyle: 'miter',
      borderWidth: 3,
      capBezierPoints: !0,
      cubicInterpolationMode: 'default',
      fill: !1,
      spanGaps: !1,
      stepped: !1,
      tension: 0,
    };

    static defaultRoutes = {
      backgroundColor: 'backgroundColor',
      borderColor: 'borderColor',
    };

    static descriptors = {
      _scriptable: !0,
      _indexable: t => t !== 'borderDash' && t !== 'fill',
    };

    constructor(t) {
      (super(),
      (this.animated = !0),
      (this.options = void 0),
      (this._chart = void 0),
      (this._loop = void 0),
      (this._fullLoop = void 0),
      (this._path = void 0),
      (this._points = void 0),
      (this._segments = void 0),
      (this._decimated = !1),
      (this._pointsUpdated = !1),
      (this._datasetIndex = void 0),
      t && Object.assign(this, t));
    }

    updateControlPoints(t, e) {
      const i = this.options;
      if (
        (i.tension || i.cubicInterpolationMode === 'monotone') &&
        !i.stepped &&
        !this._pointsUpdated
      ) {
        const s = i.spanGaps ? this._loop : this._fullLoop;
        (hi(this._points, i, t, s, e), (this._pointsUpdated = !0));
      }
    }

    set points(t) {
      ((this._points = t),
      delete this._segments,
      delete this._path,
      (this._pointsUpdated = !1));
    }

    get points() {
      return this._points;
    }

    get segments() {
      return (
        this._segments || (this._segments = zi(this, this.options.segment))
      );
    }

    first() {
      const t = this.segments;
      const e = this.points;
      return t.length && e[t[0].start];
    }

    last() {
      const t = this.segments;
      const e = this.points;
      const i = t.length;
      return i && e[t[i - 1].end];
    }

    interpolate(t, e) {
      const i = this.options;
      const s = t[e];
      const n = this.points;
      const o = Ii(this, { property: e, start: s, end: s });
      if (!o.length) {
        return;
      }
      const a = [];
      const r = (function(t) {
        return t.stepped ?
          pi :
          t.tension || t.cubicInterpolationMode === 'monotone' ?
            mi :
            gi;
      })(i);
      let l, h;
      for (l = 0, h = o.length; l < h; ++l) {
        const { start: h, end: c } = o[l];
        const d = n[h];
        const u = n[c];
        if (d === u) {
          a.push(d);
          continue;
        }
        const f = r(d, u, Math.abs((s - d[e]) / (u[e] - d[e])), i.stepped);
        ((f[e] = t[e]), a.push(f));
      }
      return a.length === 1 ? a[0] : a;
    }

    pathSegment(t, e, i) {
      return io(this)(t, this, e, i);
    }

    path(t, e, i) {
      const s = this.segments;
      const n = io(this);
      let o = this._loop;
      ((e = e || 0), (i = i || this.points.length - e));
      for (const a of s) {
        o &= n(t, this, a, { start: e, end: e + i - 1 });
      }
      return !!o;
    }

    draw(t, e, i, s) {
      const n = this.options || {};
      ((this.points || []).length &&
        n.borderWidth &&
        (t.save(), no(t, this, i, s), t.restore()),
      this.animated && ((this._pointsUpdated = !1), (this._path = void 0)));
    }
  }
  function ao(t, e, i, s) {
    const n = t.options;
    const { [i]: o } = t.getProps([i], s);
    return Math.abs(e - o) < n.radius + n.hitRadius;
  }
  function ro(t, e) {
    const {
      x: i,
      y: s,
      base: n,
      width: o,
      height: a,
    } = t.getProps(['x', 'y', 'base', 'width', 'height'], e);
    let r, l, h, c, d;
    return (
      t.horizontal ?
        ((d = a / 2),
        (r = Math.min(i, n)),
        (l = Math.max(i, n)),
        (h = s - d),
        (c = s + d)) :
        ((d = o / 2),
        (r = i - d),
        (l = i + d),
        (h = Math.min(s, n)),
        (c = Math.max(s, n))),
      { left: r, top: h, right: l, bottom: c }
    );
  }
  function lo(t, e, i, s) {
    return t ? 0 : Z(e, i, s);
  }
  function ho(t) {
    const e = ro(t);
    const i = e.right - e.left;
    const s = e.bottom - e.top;
    const n = (function(t, e, i) {
      const s = t.options.borderWidth;
      const n = t.borderSkipped;
      const o = Mi(s);
      return {
        t: lo(n.top, o.top, 0, i),
        r: lo(n.right, o.right, 0, e),
        b: lo(n.bottom, o.bottom, 0, i),
        l: lo(n.left, o.left, 0, e),
      };
    })(t, i / 2, s / 2);
    const a = (function(t, e, i) {
      const { enableBorderRadius: s } = t.getProps(['enableBorderRadius']);
      const n = t.options.borderRadius;
      const a = wi(n);
      const r = Math.min(e, i);
      const l = t.borderSkipped;
      const h = s || o(n);
      return {
        topLeft: lo(!h || l.top || l.left, a.topLeft, 0, r),
        topRight: lo(!h || l.top || l.right, a.topRight, 0, r),
        bottomLeft: lo(!h || l.bottom || l.left, a.bottomLeft, 0, r),
        bottomRight: lo(!h || l.bottom || l.right, a.bottomRight, 0, r),
      };
    })(t, i / 2, s / 2);
    return {
      outer: { x: e.left, y: e.top, w: i, h: s, radius: a },
      inner: {
        x: e.left + n.l,
        y: e.top + n.t,
        w: i - n.l - n.r,
        h: s - n.t - n.b,
        radius: {
          topLeft: Math.max(0, a.topLeft - Math.max(n.t, n.l)),
          topRight: Math.max(0, a.topRight - Math.max(n.t, n.r)),
          bottomLeft: Math.max(0, a.bottomLeft - Math.max(n.b, n.l)),
          bottomRight: Math.max(0, a.bottomRight - Math.max(n.b, n.r)),
        },
      },
    };
  }
  function co(t, e, i, s) {
    const n = e === null;
    const o = i === null;
    const a = t && !(n && o) && ro(t, s);
    return a && (n || tt(e, a.left, a.right)) && (o || tt(i, a.top, a.bottom));
  }
  function uo(t, e) {
    t.rect(e.x, e.y, e.w, e.h);
  }
  function fo(t, e, i = {}) {
    const s = t.x !== i.x ? -e : 0;
    const n = t.y !== i.y ? -e : 0;
    const o = (t.x + t.w !== i.x + i.w ? e : 0) - s;
    const a = (t.y + t.h !== i.y + i.h ? e : 0) - n;
    return { x: t.x + s, y: t.y + n, w: t.w + o, h: t.h + a, radius: t.radius };
  }
  const go = Object.freeze({
    __proto__: null,
    ArcElement: class extends $s {
      static id = 'arc';
      static defaults = {
        borderAlign: 'center',
        borderColor: '#fff',
        borderDash: [],
        borderDashOffset: 0,
        borderJoinStyle: void 0,
        borderRadius: 0,
        borderWidth: 2,
        offset: 0,
        spacing: 0,
        angle: void 0,
        circular: !0,
        selfJoin: !1,
      };

      static defaultRoutes = { backgroundColor: 'backgroundColor' };
      static descriptors = {
        _scriptable: !0,
        _indexable: t => t !== 'borderDash',
      };

      circumference;
      endAngle;
      fullCircles;
      innerRadius;
      outerRadius;
      pixelMargin;
      startAngle;
      constructor(t) {
        (super(),
        (this.options = void 0),
        (this.circumference = void 0),
        (this.startAngle = void 0),
        (this.endAngle = void 0),
        (this.innerRadius = void 0),
        (this.outerRadius = void 0),
        (this.pixelMargin = 0),
        (this.fullCircles = 0),
        t && Object.assign(this, t));
      }

      inRange(t, e, i) {
        const s = this.getProps(['x', 'y'], i);
        const { angle: n, distance: o } = X(s, { x: t, y: e });
        const {
          startAngle: a,
          endAngle: r,
          innerRadius: h,
          outerRadius: c,
          circumference: d,
        } = this.getProps(
          [
            'startAngle',
            'endAngle',
            'innerRadius',
            'outerRadius',
            'circumference',
          ],
          i,
        );
        const u = (this.options.spacing + this.options.borderWidth) / 2;
        const f = l(d, r - a);
        const g = J(n, a, r) && a !== r;
        const p = f >= O || g;
        const m = tt(o, h + u, c + u);
        return p && m;
      }

      getCenterPoint(t) {
        const {
          x: e,
          y: i,
          startAngle: s,
          endAngle: n,
          innerRadius: o,
          outerRadius: a,
        } = this.getProps(
          ['x', 'y', 'startAngle', 'endAngle', 'innerRadius', 'outerRadius'],
          t,
        );
        const { offset: r, spacing: l } = this.options;
        const h = (s + n) / 2;
        const c = (o + a + l + r) / 2;
        return { x: e + Math.cos(h) * c, y: i + Math.sin(h) * c };
      }

      tooltipPosition(t) {
        return this.getCenterPoint(t);
      }

      draw(t) {
        const { options: e, circumference: i } = this;
        const s = (e.offset || 0) / 4;
        const n = (e.spacing || 0) / 2;
        const o = e.circular;
        if (
          ((this.pixelMargin = e.borderAlign === 'inner' ? 0.33 : 0),
          (this.fullCircles = i > O ? Math.floor(i / O) : 0),
          i === 0 || this.innerRadius < 0 || this.outerRadius < 0)
        ) {
          return;
        }
        t.save();
        const a = (this.startAngle + this.endAngle) / 2;
        t.translate(Math.cos(a) * s, Math.sin(a) * s);
        const r = s * (1 - Math.sin(Math.min(C, i || 0)));
        ((t.fillStyle = e.backgroundColor),
        (t.strokeStyle = e.borderColor),
        (function(t, e, i, s, n) {
          const { fullCircles: o, startAngle: a, circumference: r } = e;
          let l = e.endAngle;
          if (o) {
            Kn(t, e, i, s, l, n);
            for (let e = 0; e < o; ++e) {
              t.fill();
            }
            isNaN(r) || (l = a + (r % O || O));
          }
          (Kn(t, e, i, s, l, n), t.fill());
        })(t, this, r, n, o),
        Gn(t, this, r, n, o),
        t.restore());
      }
    },
    BarElement: class extends $s {
      static id = 'bar';
      static defaults = {
        borderSkipped: 'start',
        borderWidth: 0,
        borderRadius: 0,
        inflateAmount: 'auto',
        pointStyle: void 0,
      };

      static defaultRoutes = {
        backgroundColor: 'backgroundColor',
        borderColor: 'borderColor',
      };

      constructor(t) {
        (super(),
        (this.options = void 0),
        (this.horizontal = void 0),
        (this.base = void 0),
        (this.width = void 0),
        (this.height = void 0),
        (this.inflateAmount = void 0),
        t && Object.assign(this, t));
      }

      draw(t) {
        const {
          inflateAmount: e,
          options: { borderColor: i, backgroundColor: s },
        } = this;
        const { inner: n, outer: o } = ho(this);
        const a =
            (r = o.radius).topLeft ||
            r.topRight ||
            r.bottomLeft ||
            r.bottomRight ?
              He :
              uo;
        let r;
        (t.save(),
        (o.w === n.w && o.h === n.h) ||
            (t.beginPath(),
            a(t, fo(o, e, n)),
            t.clip(),
            a(t, fo(n, -e, o)),
            (t.fillStyle = i),
            t.fill('evenodd')),
        t.beginPath(),
        a(t, fo(n, e)),
        (t.fillStyle = s),
        t.fill(),
        t.restore());
      }

      inRange(t, e, i) {
        return co(this, t, e, i);
      }

      inXRange(t, e) {
        return co(this, t, null, e);
      }

      inYRange(t, e) {
        return co(this, null, t, e);
      }

      getCenterPoint(t) {
        const {
          x: e,
          y: i,
          base: s,
          horizontal: n,
        } = this.getProps(['x', 'y', 'base', 'horizontal'], t);
        return { x: n ? (e + s) / 2 : e, y: n ? i : (i + s) / 2 };
      }

      getRange(t) {
        return t === 'x' ? this.width / 2 : this.height / 2;
      }
    },
    LineElement: oo,
    PointElement: class extends $s {
      static id = 'point';
      parsed;
      skip;
      stop;
      static defaults = {
        borderWidth: 1,
        hitRadius: 1,
        hoverBorderWidth: 1,
        hoverRadius: 4,
        pointStyle: 'circle',
        radius: 3,
        rotation: 0,
      };

      static defaultRoutes = {
        backgroundColor: 'backgroundColor',
        borderColor: 'borderColor',
      };

      constructor(t) {
        (super(),
        (this.options = void 0),
        (this.parsed = void 0),
        (this.skip = void 0),
        (this.stop = void 0),
        t && Object.assign(this, t));
      }

      inRange(t, e, i) {
        const s = this.options;
        const { x: n, y: o } = this.getProps(['x', 'y'], i);
        return (
          Math.pow(t - n, 2) + Math.pow(e - o, 2) <
          Math.pow(s.hitRadius + s.radius, 2)
        );
      }

      inXRange(t, e) {
        return ao(this, t, 'x', e);
      }

      inYRange(t, e) {
        return ao(this, t, 'y', e);
      }

      getCenterPoint(t) {
        const { x: e, y: i } = this.getProps(['x', 'y'], t);
        return { x: e, y: i };
      }

      size(t) {
        let e = (t = t || this.options || {}).radius || 0;
        e = Math.max(e, (e && t.hoverRadius) || 0);
        return 2 * (e + ((e && t.borderWidth) || 0));
      }

      draw(t, e) {
        const i = this.options;
        this.skip ||
          i.radius < 0.1 ||
          !Re(this, e, this.size(i) / 2) ||
          ((t.strokeStyle = i.borderColor),
          (t.lineWidth = i.borderWidth),
          (t.fillStyle = i.backgroundColor),
          Le(t, i, this.x, this.y));
      }

      getRange() {
        const t = this.options || {};
        return t.radius + t.hitRadius;
      }
    },
  });
  function po(t, e, i, s) {
    const n = t.indexOf(e);
    if (n === -1) {
      return ((t, e, i, s) => (
        typeof e === 'string' ?
          ((i = t.push(e) - 1), s.unshift({ index: i, label: e })) :
          isNaN(e) && (i = null),
        i
      ))(t, e, i, s);
    }
    return n !== t.lastIndexOf(e) ? i : n;
  }
  function mo(t) {
    const e = this.getLabels();
    return t >= 0 && t < e.length ? e[t] : t;
  }
  function xo(t, e, { horizontal: i, minRotation: s }) {
    const n = $(s);
    const o = (i ? Math.sin(n) : Math.cos(n)) || 0.001;
    const a = 0.75 * e * (`${t}`).length;
    return Math.min(e / o, a);
  }
  class bo extends tn {
    constructor(t) {
      (super(t),
      (this.start = void 0),
      (this.end = void 0),
      (this._startValue = void 0),
      (this._endValue = void 0),
      (this._valueRange = 0));
    }

    parse(t, e) {
      return s(t) ||
        ((typeof t === 'number' || t instanceof Number) && !isFinite(+t)) ?
        null :
        +t;
    }

    handleTickRangeOptions() {
      const { beginAtZero: t } = this.options;
      const { minDefined: e, maxDefined: i } = this.getUserBounds();
      let { min: s, max: n } = this;
      const o = t => (s = e ? s : t);
      const a = t => (n = i ? n : t);
      if (t) {
        const t = F(s);
        const e = F(n);
        t < 0 && e < 0 ? a(0) : t > 0 && e > 0 && o(0);
      }
      if (s === n) {
        const e = n === 0 ? 1 : Math.abs(0.05 * n);
        (a(n + e), t || o(s - e));
      }
      ((this.min = s), (this.max = n));
    }

    getTickLimit() {
      const t = this.options.ticks;
      let e;
      let { maxTicksLimit: i, stepSize: s } = t;
      return (
        s ?
          ((e = Math.ceil(this.max / s) - Math.floor(this.min / s) + 1),
          e > 1e3 &&
              (console.warn(
                `scales.${this.id}.ticks.stepSize: ${s} would result generating up to ${e} ticks. Limiting to 1000.`,
              ),
              (e = 1e3))) :
          ((e = this.computeTickLimit()), (i = i || 11)),
        i && (e = Math.min(i, e)),
        e
      );
    }

    computeTickLimit() {
      return Number.POSITIVE_INFINITY;
    }

    buildTicks() {
      const t = this.options;
      const e = t.ticks;
      let i = this.getTickLimit();
      i = Math.max(2, i);
      const n = (function(t, e) {
        const i = [];
        const {
          bounds: n,
          step: o,
          min: a,
          max: r,
          precision: l,
          count: h,
          maxTicks: c,
          maxDigits: d,
          includeBounds: u,
        } = t;
        const f = o || 1;
        const g = c - 1;
        const { min: p, max: m } = e;
        const x = !s(a);
        const b = !s(r);
        const _ = !s(h);
        const y = (m - p) / (d + 1);
        let v;
        let M;
        let w;
        let k;
        let S = B((m - p) / g / f) * f;
        if (S < 1e-14 && !x && !b) {
          return [{ value: p }, { value: m }];
        }
        ((k = Math.ceil(m / S) - Math.floor(p / S)),
        k > g && (S = B((k * S) / g / f) * f),
        s(l) || ((v = Math.pow(10, l)), (S = Math.ceil(S * v) / v)),
        n === 'ticks' ?
          ((M = Math.floor(p / S) * S), (w = Math.ceil(m / S) * S)) :
          ((M = p), (w = m)),
        x && b && o && H((r - a) / o, S / 1e3) ?
          ((k = Math.round(Math.min((r - a) / S, c))),
          (S = (r - a) / k),
          (M = a),
          (w = r)) :
          _ ?
            ((M = x ? a : M),
            (w = b ? r : w),
            (k = h - 1),
            (S = (w - M) / k)) :
            ((k = (w - M) / S),
            (k = V(k, Math.round(k), S / 1e3) ?
              Math.round(k) :
              Math.ceil(k))));
        const P = Math.max(U(S), U(M));
        ((v = Math.pow(10, s(l) ? P : l)),
        (M = Math.round(M * v) / v),
        (w = Math.round(w * v) / v));
        let D = 0;
        for (
          x &&
          (u && M !== a ?
            (i.push({ value: a }),
            M < a && D++,
            V(Math.round((M + D * S) * v) / v, a, xo(a, y, t)) && D++) :
            M < a && D++);
          D < k;
          ++D
        ) {
          const t = Math.round((M + D * S) * v) / v;
          if (b && t > r) {
            break;
          }
          i.push({ value: t });
        }
        return (
          b && u && w !== r ?
            i.length && V(i[i.length - 1].value, r, xo(r, y, t)) ?
              (i[i.length - 1].value = r) :
              i.push({ value: r }) :
            (b && w !== r) || i.push({ value: w }),
          i
        );
      })(
        {
          maxTicks: i,
          bounds: t.bounds,
          min: t.min,
          max: t.max,
          precision: e.precision,
          step: e.stepSize,
          count: e.count,
          maxDigits: this._maxDigits(),
          horizontal: this.isHorizontal(),
          minRotation: e.minRotation || 0,
          includeBounds: !1 !== e.includeBounds,
        },
        this._range || this,
      );
      return (
        t.bounds === 'ticks' && j(n, this, 'value'),
        t.reverse ?
          (n.reverse(), (this.start = this.max), (this.end = this.min)) :
          ((this.start = this.min), (this.end = this.max)),
        n
      );
    }

    configure() {
      const t = this.ticks;
      let e = this.min;
      let i = this.max;
      if ((super.configure(), this.options.offset && t.length)) {
        const s = (i - e) / Math.max(t.length - 1, 1) / 2;
        ((e -= s), (i += s));
      }
      ((this._startValue = e),
      (this._endValue = i),
      (this._valueRange = i - e));
    }

    getLabelForValue(t) {
      return ne(t, this.chart.options.locale, this.options.ticks.format);
    }
  }
  class _o extends bo {
    static id = 'linear';
    static defaults = { ticks: { callback: ae.formatters.numeric } };
    determineDataLimits() {
      const { min: t, max: e } = this.getMinMax(!0);
      ((this.min = a(t) ? t : 0),
      (this.max = a(e) ? e : 1),
      this.handleTickRangeOptions());
    }

    computeTickLimit() {
      const t = this.isHorizontal();
      const e = t ? this.width : this.height;
      const i = $(this.options.ticks.minRotation);
      const s = (t ? Math.sin(i) : Math.cos(i)) || 0.001;
      const n = this._resolveTickFontOptions(0);
      return Math.ceil(e / Math.min(40, n.lineHeight / s));
    }

    getPixelForValue(t) {
      return t === null ?
        NaN :
        this.getPixelForDecimal((t - this._startValue) / this._valueRange);
    }

    getValueForPixel(t) {
      return this._startValue + this.getDecimalForPixel(t) * this._valueRange;
    }
  }
  const yo = t => Math.floor(z(t));
  const vo = (t, e) => Math.pow(10, yo(t) + e);
  function Mo(t) {
    return t / Math.pow(10, yo(t)) === 1;
  }
  function wo(t, e, i) {
    const s = Math.pow(10, i);
    const n = Math.floor(t / s);
    return Math.ceil(e / s) - n;
  }
  function ko(t, { min: e, max: i }) {
    e = r(t.min, e);
    const s = [];
    const n = yo(e);
    let o = (function(t, e) {
      let i = yo(e - t);
      for (; wo(t, e, i) > 10;) {
        i++;
      }
      for (; wo(t, e, i) < 10;) {
        i--;
      }
      return Math.min(i, yo(t));
    })(e, i);
    let a = o < 0 ? Math.pow(10, Math.abs(o)) : 1;
    const l = Math.pow(10, o);
    const h = n > o ? Math.pow(10, n) : 0;
    const c = Math.round((e - h) * a) / a;
    const d = Math.floor((e - h) / l / 10) * l * 10;
    let u = Math.floor((c - d) / Math.pow(10, o));
    let f = r(t.min, Math.round((h + d + u * Math.pow(10, o)) * a) / a);
    for (; f < i;) {
      (s.push({ value: f, major: Mo(f), significand: u }),
      u >= 10 ? (u = u < 15 ? 15 : 20) : u++,
      u >= 20 && (o++, (u = 2), (a = o >= 0 ? 1 : a)),
      (f = Math.round((h + d + u * Math.pow(10, o)) * a) / a));
    }
    const g = r(t.max, f);
    return (s.push({ value: g, major: Mo(g), significand: u }), s);
  }
  class So extends tn {
    static id = 'logarithmic';
    static defaults = {
      ticks: { callback: ae.formatters.logarithmic, major: { enabled: !0 } },
    };

    constructor(t) {
      (super(t),
      (this.start = void 0),
      (this.end = void 0),
      (this._startValue = void 0),
      (this._valueRange = 0));
    }

    parse(t, e) {
      const i = bo.prototype.parse.apply(this, [t, e]);
      if (i !== 0) {
        return a(i) && i > 0 ? i : null;
      }
      this._zero = !0;
    }

    determineDataLimits() {
      const { min: t, max: e } = this.getMinMax(!0);
      ((this.min = a(t) ? Math.max(0, t) : null),
      (this.max = a(e) ? Math.max(0, e) : null),
      this.options.beginAtZero && (this._zero = !0),
      this._zero &&
          this.min !== this._suggestedMin &&
          !a(this._userMin) &&
          (this.min =
            t === vo(this.min, 0) ? vo(this.min, -1) : vo(this.min, 0)),
      this.handleTickRangeOptions());
    }

    handleTickRangeOptions() {
      const { minDefined: t, maxDefined: e } = this.getUserBounds();
      let i = this.min;
      let s = this.max;
      const n = e => (i = t ? i : e);
      const o = t => (s = e ? s : t);
      (i === s && (i <= 0 ? (n(1), o(10)) : (n(vo(i, -1)), o(vo(s, 1)))),
      i <= 0 && n(vo(s, -1)),
      s <= 0 && o(vo(i, 1)),
      (this.min = i),
      (this.max = s));
    }

    buildTicks() {
      const t = this.options;
      const e = ko({ min: this._userMin, max: this._userMax }, this);
      return (
        t.bounds === 'ticks' && j(e, this, 'value'),
        t.reverse ?
          (e.reverse(), (this.start = this.max), (this.end = this.min)) :
          ((this.start = this.min), (this.end = this.max)),
        e
      );
    }

    getLabelForValue(t) {
      return void 0 === t ?
        '0' :
        ne(t, this.chart.options.locale, this.options.ticks.format);
    }

    configure() {
      const t = this.min;
      (super.configure(),
      (this._startValue = z(t)),
      (this._valueRange = z(this.max) - z(t)));
    }

    getPixelForValue(t) {
      return (
        (void 0 !== t && t !== 0) || (t = this.min),
        t === null || isNaN(t) ?
          NaN :
          this.getPixelForDecimal(
            t === this.min ? 0 : (z(t) - this._startValue) / this._valueRange,
          )
      );
    }

    getValueForPixel(t) {
      const e = this.getDecimalForPixel(t);
      return Math.pow(10, this._startValue + e * this._valueRange);
    }
  }
  function Po(t) {
    const e = t.ticks;
    if (e.display && t.display) {
      const t = ki(e.backdropPadding);
      return l(e.font && e.font.size, ue.font.size) + t.height;
    }
    return 0;
  }
  function Do(t, e, i, s, n) {
    return t === s || t === n ?
      { start: e - i / 2, end: e + i / 2 } :
      t < s || t > n ?
        { start: e - i, end: e } :
        { start: e, end: e + i };
  }
  function Co(t) {
    const e = {
      l: t.left + t._padding.left,
      r: t.right - t._padding.right,
      t: t.top + t._padding.top,
      b: t.bottom - t._padding.bottom,
    };
    const i = Object.assign({}, e);
    const s = [];
    const o = [];
    const a = t._pointLabels.length;
    const r = t.options.pointLabels;
    const l = r.centerPointLabels ? C / a : 0;
    for (let u = 0; u < a; u++) {
      const a = r.setContext(t.getPointLabelContext(u));
      o[u] = a.padding;
      const f = t.getPointPosition(u, t.drawingArea + o[u], l);
      const g = Si(a.font);
      const p =
          ((h = t.ctx),
          (c = g),
          (d = n((d = t._pointLabels[u])) ? d : [d]),
          { w: Oe(h, c.string, d), h: d.length * c.lineHeight });
      s[u] = p;
      const m = G(t.getIndexAngle(u) + l);
      const x = Math.round(Y(m));
      Oo(i, e, m, Do(x, f.x, p.w, 0, 180), Do(x, f.y, p.h, 90, 270));
    }
    let h, c, d;
    (t.setCenterPoint(e.l - i.l, i.r - e.r, e.t - i.t, i.b - e.b),
    (t._pointLabelItems = (function(t, e, i) {
      const s = [];
      const n = t._pointLabels.length;
      const o = t.options;
      const { centerPointLabels: a, display: r } = o.pointLabels;
      const l = { extra: Po(o) / 2, additionalAngle: a ? C / n : 0 };
      let h;
      for (let o = 0; o < n; o++) {
        ((l.padding = i[o]), (l.size = e[o]));
        const n = Ao(t, o, l);
        (s.push(n),
        r === 'auto' && ((n.visible = To(n, h)), n.visible && (h = n)));
      }
      return s;
    })(t, s, o)));
  }
  function Oo(t, e, i, s, n) {
    const o = Math.abs(Math.sin(i));
    const a = Math.abs(Math.cos(i));
    let r = 0;
    let l = 0;
    (s.start < e.l ?
      ((r = (e.l - s.start) / o), (t.l = Math.min(t.l, e.l - r))) :
      s.end > e.r &&
        ((r = (s.end - e.r) / o), (t.r = Math.max(t.r, e.r + r))),
    n.start < e.t ?
      ((l = (e.t - n.start) / a), (t.t = Math.min(t.t, e.t - l))) :
      n.end > e.b &&
          ((l = (n.end - e.b) / a), (t.b = Math.max(t.b, e.b + l))));
  }
  function Ao(t, e, i) {
    const s = t.drawingArea;
    const { extra: n, additionalAngle: o, padding: a, size: r } = i;
    const l = t.getPointPosition(e, s + n + a, o);
    const h = Math.round(Y(G(l.angle + E)));
    const c = (function(t, e, i) {
      i === 90 || i === 270 ? (t -= e / 2) : (i > 270 || i < 90) && (t -= e);
      return t;
    })(l.y, r.h, h);
    const d = (function(t) {
      if (t === 0 || t === 180) {
        return 'center';
      }
      if (t < 180) {
        return 'left';
      }
      return 'right';
    })(h);
    const u = (function(t, e, i) {
      i === 'right' ? (t -= e) : i === 'center' && (t -= e / 2);
      return t;
    })(l.x, r.w, d);
    return {
      visible: !0,
      x: l.x,
      y: c,
      textAlign: d,
      left: u,
      top: c,
      right: u + r.w,
      bottom: c + r.h,
    };
  }
  function To(t, e) {
    if (!e) {
      return !0;
    }
    const { left: i, top: s, right: n, bottom: o } = t;
    return !(
      Re({ x: i, y: s }, e) ||
      Re({ x: i, y: o }, e) ||
      Re({ x: n, y: s }, e) ||
      Re({ x: n, y: o }, e)
    );
  }
  function Lo(t, e, i) {
    const { left: n, top: o, right: a, bottom: r } = i;
    const { backdropColor: l } = e;
    if (!s(l)) {
      const i = wi(e.borderRadius);
      const s = ki(e.backdropPadding);
      t.fillStyle = l;
      const h = n - s.left;
      const c = o - s.top;
      const d = a - n + s.width;
      const u = r - o + s.height;
      Object.values(i).some(t => t !== 0) ?
        (t.beginPath(),
        He(t, { x: h, y: c, w: d, h: u, radius: i }),
        t.fill()) :
        t.fillRect(h, c, d, u);
    }
  }
  function Eo(t, e, i, s) {
    const { ctx: n } = t;
    if (i) {
      n.arc(t.xCenter, t.yCenter, e, 0, O);
    } else {
      let i = t.getPointPosition(0, e);
      n.moveTo(i.x, i.y);
      for (let o = 1; o < s; o++) {
        ((i = t.getPointPosition(o, e)), n.lineTo(i.x, i.y));
      }
    }
  }
  class Ro extends bo {
    static id = 'radialLinear';
    static defaults = {
      display: !0,
      animate: !0,
      position: 'chartArea',
      angleLines: {
        display: !0,
        lineWidth: 1,
        borderDash: [],
        borderDashOffset: 0,
      },
      grid: { circular: !1 },
      startAngle: 0,
      ticks: { showLabelBackdrop: !0, callback: ae.formatters.numeric },
      pointLabels: {
        backdropColor: void 0,
        backdropPadding: 2,
        display: !0,
        font: { size: 10 },
        callback: t => t,
        padding: 5,
        centerPointLabels: !1,
      },
    };

    static defaultRoutes = {
      'angleLines.color': 'borderColor',
      'pointLabels.color': 'color',
      'ticks.color': 'color',
    };

    static descriptors = { angleLines: { _fallback: 'grid' } };
    constructor(t) {
      (super(t),
      (this.xCenter = void 0),
      (this.yCenter = void 0),
      (this.drawingArea = void 0),
      (this._pointLabels = []),
      (this._pointLabelItems = []));
    }

    setDimensions() {
      const t = (this._padding = ki(Po(this.options) / 2));
      const e = (this.width = this.maxWidth - t.width);
      const i = (this.height = this.maxHeight - t.height);
      ((this.xCenter = Math.floor(this.left + e / 2 + t.left)),
      (this.yCenter = Math.floor(this.top + i / 2 + t.top)),
      (this.drawingArea = Math.floor(Math.min(e, i) / 2)));
    }

    determineDataLimits() {
      const { min: t, max: e } = this.getMinMax(!1);
      ((this.min = a(t) && !isNaN(t) ? t : 0),
      (this.max = a(e) && !isNaN(e) ? e : 0),
      this.handleTickRangeOptions());
    }

    computeTickLimit() {
      return Math.ceil(this.drawingArea / Po(this.options));
    }

    generateTickLabels(t) {
      (bo.prototype.generateTickLabels.call(this, t),
      (this._pointLabels = this.getLabels()
        .map((t, e) => {
          const i = d(this.options.pointLabels.callback, [t, e], this);
          return i || i === 0 ? i : '';
        })
        .filter((t, e) => this.chart.getDataVisibility(e))));
    }

    fit() {
      const t = this.options;
      t.display && t.pointLabels.display ?
        Co(this) :
        this.setCenterPoint(0, 0, 0, 0);
    }

    setCenterPoint(t, e, i, s) {
      ((this.xCenter += Math.floor((t - e) / 2)),
      (this.yCenter += Math.floor((i - s) / 2)),
      (this.drawingArea -= Math.min(
        this.drawingArea / 2,
        Math.max(t, e, i, s),
      )));
    }

    getIndexAngle(t) {
      return G(
        t * (O / (this._pointLabels.length || 1)) +
          $(this.options.startAngle || 0),
      );
    }

    getDistanceFromCenterForValue(t) {
      if (s(t)) {
        return NaN;
      }
      const e = this.drawingArea / (this.max - this.min);
      return this.options.reverse ? (this.max - t) * e : (t - this.min) * e;
    }

    getValueForDistanceFromCenter(t) {
      if (s(t)) {
        return NaN;
      }
      const e = t / (this.drawingArea / (this.max - this.min));
      return this.options.reverse ? this.max - e : this.min + e;
    }

    getPointLabelContext(t) {
      const e = this._pointLabels || [];
      if (t >= 0 && t < e.length) {
        const i = e[t];
        return (function(t, e, i) {
          return Ci(t, { label: i, index: e, type: 'pointLabel' });
        })(this.getContext(), t, i);
      }
    }

    getPointPosition(t, e, i = 0) {
      const s = this.getIndexAngle(t) - E + i;
      return {
        x: Math.cos(s) * e + this.xCenter,
        y: Math.sin(s) * e + this.yCenter,
        angle: s,
      };
    }

    getPointPositionForValue(t, e) {
      return this.getPointPosition(t, this.getDistanceFromCenterForValue(e));
    }

    getBasePosition(t) {
      return this.getPointPositionForValue(t || 0, this.getBaseValue());
    }

    getPointLabelPosition(t) {
      const { left: e, top: i, right: s, bottom: n } = this._pointLabelItems[t];
      return { left: e, top: i, right: s, bottom: n };
    }

    drawBackground() {
      const {
        backgroundColor: t,
        grid: { circular: e },
      } = this.options;
      if (t) {
        const i = this.ctx;
        (i.save(),
        i.beginPath(),
        Eo(
          this,
          this.getDistanceFromCenterForValue(this._endValue),
          e,
          this._pointLabels.length,
        ),
        i.closePath(),
        (i.fillStyle = t),
        i.fill(),
        i.restore());
      }
    }

    drawGrid() {
      const t = this.ctx;
      const e = this.options;
      const { angleLines: i, grid: s, border: n } = e;
      const o = this._pointLabels.length;
      let a, r, l;
      if (
        (e.pointLabels.display &&
          (function(t, e) {
            const {
              ctx: i,
              options: { pointLabels: s },
            } = t;
            for (let n = e - 1; n >= 0; n--) {
              const e = t._pointLabelItems[n];
              if (!e.visible) {
                continue;
              }
              const o = s.setContext(t.getPointLabelContext(n));
              Lo(i, o, e);
              const a = Si(o.font);
              const { x: r, y: l, textAlign: h } = e;
              Ne(i, t._pointLabels[n], r, l + a.lineHeight / 2, a, {
                color: o.color,
                textAlign: h,
                textBaseline: 'middle',
              });
            }
          })(this, o),
        s.display &&
          this.ticks.forEach((t, e) => {
            if (e !== 0 || (e === 0 && this.min < 0)) {
              r = this.getDistanceFromCenterForValue(t.value);
              const i = this.getContext(e);
              const a = s.setContext(i);
              const l = n.setContext(i);
              !(function(t, e, i, s, n) {
                const o = t.ctx;
                const a = e.circular;
                const { color: r, lineWidth: l } = e;
                (!a && !s) ||
                  !r ||
                  !l ||
                  i < 0 ||
                  (o.save(),
                  (o.strokeStyle = r),
                  (o.lineWidth = l),
                  o.setLineDash(n.dash || []),
                  (o.lineDashOffset = n.dashOffset),
                  o.beginPath(),
                  Eo(t, i, a, s),
                  o.closePath(),
                  o.stroke(),
                  o.restore());
              })(this, a, r, o, l);
            }
          }),
        i.display)
      ) {
        for (t.save(), a = o - 1; a >= 0; a--) {
          const s = i.setContext(this.getPointLabelContext(a));
          const { color: n, lineWidth: o } = s;
          o &&
            n &&
            ((t.lineWidth = o),
            (t.strokeStyle = n),
            t.setLineDash(s.borderDash),
            (t.lineDashOffset = s.borderDashOffset),
            (r = this.getDistanceFromCenterForValue(
              e.reverse ? this.min : this.max,
            )),
            (l = this.getPointPosition(a, r)),
            t.beginPath(),
            t.moveTo(this.xCenter, this.yCenter),
            t.lineTo(l.x, l.y),
            t.stroke());
        }
        t.restore();
      }
    }

    drawBorder() {}
    drawLabels() {
      const t = this.ctx;
      const e = this.options;
      const i = e.ticks;
      if (!i.display) {
        return;
      }
      const s = this.getIndexAngle(0);
      let n, o;
      (t.save(),
      t.translate(this.xCenter, this.yCenter),
      t.rotate(s),
      (t.textAlign = 'center'),
      (t.textBaseline = 'middle'),
      this.ticks.forEach((s, a) => {
        if (a === 0 && this.min >= 0 && !e.reverse) {
          return;
        }
        const r = i.setContext(this.getContext(a));
        const l = Si(r.font);
        if (
          ((n = this.getDistanceFromCenterForValue(this.ticks[a].value)),
          r.showLabelBackdrop)
        ) {
          ((t.font = l.string),
          (o = t.measureText(s.label).width),
          (t.fillStyle = r.backdropColor));
          const e = ki(r.backdropPadding);
          t.fillRect(
            -o / 2 - e.left,
            -n - l.size / 2 - e.top,
            o + e.width,
            l.size + e.height,
          );
        }
        Ne(t, s.label, 0, -n, l, {
          color: r.color,
          strokeColor: r.textStrokeColor,
          strokeWidth: r.textStrokeWidth,
        });
      }),
      t.restore());
    }

    drawTitle() {}
  }
  const Io = {
    millisecond: { common: !0, size: 1, steps: 1e3 },
    second: { common: !0, size: 1e3, steps: 60 },
    minute: { common: !0, size: 6e4, steps: 60 },
    hour: { common: !0, size: 36e5, steps: 24 },
    day: { common: !0, size: 864e5, steps: 30 },
    week: { common: !1, size: 6048e5, steps: 4 },
    month: { common: !0, size: 2628e6, steps: 12 },
    quarter: { common: !1, size: 7884e6, steps: 4 },
    year: { common: !0, size: 3154e7 },
  };
  const zo = Object.keys(Io);
  function Fo(t, e) {
    return t - e;
  }
  function Vo(t, e) {
    if (s(e)) {
      return null;
    }
    const i = t._adapter;
    const { parser: n, round: o, isoWeekday: r } = t._parseOpts;
    let l = e;
    return (
      typeof n === 'function' && (l = n(l)),
      a(l) || (l = typeof n === 'string' ? i.parse(l, n) : i.parse(l)),
      l === null ?
        null :
        (o &&
            (l =
              o !== 'week' || (!N(r) && !0 !== r) ?
                i.startOf(l, o) :
                i.startOf(l, 'isoWeek', r)),
        +l)
    );
  }
  function Bo(t, e, i, s) {
    const n = zo.length;
    for (let o = zo.indexOf(t); o < n - 1; ++o) {
      const t = Io[zo[o]];
      const n = t.steps ? t.steps : Number.MAX_SAFE_INTEGER;
      if (t.common && Math.ceil((i - e) / (n * t.size)) <= s) {
        return zo[o];
      }
    }
    return zo[n - 1];
  }
  function Wo(t, e, i) {
    if (i) {
      if (i.length) {
        const { lo: s, hi: n } = et(i, e);
        t[i[s] >= e ? i[s] : i[n]] = !0;
      }
    } else {
      t[e] = !0;
    }
  }
  function No(t, e, i) {
    const s = [];
    const n = {};
    const o = e.length;
    let a, r;
    for (a = 0; a < o; ++a) {
      ((r = e[a]), (n[r] = a), s.push({ value: r, major: !1 }));
    }
    return o !== 0 && i ?
      (function(t, e, i, s) {
        const n = t._adapter;
        const o = +n.startOf(e[0].value, s);
        const a = e[e.length - 1].value;
        let r, l;
        for (r = o; r <= a; r = +n.add(r, 1, s)) {
          ((l = i[r]), l >= 0 && (e[l].major = !0));
        }
        return e;
      })(t, s, n, i) :
      s;
  }
  class Ho extends tn {
    static id = 'time';
    static defaults = {
      bounds: 'data',
      adapters: {},
      time: {
        parser: !1,
        unit: !1,
        round: !1,
        isoWeekday: !1,
        minUnit: 'millisecond',
        displayFormats: {},
      },
      ticks: { source: 'auto', callback: !1, major: { enabled: !1 } },
    };

    constructor(t) {
      (super(t),
      (this._cache = { data: [], labels: [], all: [] }),
      (this._unit = 'day'),
      (this._majorUnit = void 0),
      (this._offsets = {}),
      (this._normalized = !1),
      (this._parseOpts = void 0));
    }

    init(t, e = {}) {
      const i = t.time || (t.time = {});
      const s = (this._adapter = new In._date(t.adapters.date));
      (s.init(e),
      b(i.displayFormats, s.formats()),
      (this._parseOpts = {
        parser: i.parser,
        round: i.round,
        isoWeekday: i.isoWeekday,
      }),
      super.init(t),
      (this._normalized = e.normalized));
    }

    parse(t, e) {
      return void 0 === t ? null : Vo(this, t);
    }

    beforeLayout() {
      (super.beforeLayout(), (this._cache = { data: [], labels: [], all: [] }));
    }

    determineDataLimits() {
      const t = this.options;
      const e = this._adapter;
      const i = t.time.unit || 'day';
      let {
        min: s,
        max: n,
        minDefined: o,
        maxDefined: r,
      } = this.getUserBounds();
      function l(t) {
        (o || isNaN(t.min) || (s = Math.min(s, t.min)),
        r || isNaN(t.max) || (n = Math.max(n, t.max)));
      }
      ((o && r) ||
        (l(this._getLabelBounds()),
        (t.bounds === 'ticks' && t.ticks.source === 'labels') ||
          l(this.getMinMax(!1))),
      (s = a(s) && !isNaN(s) ? s : +e.startOf(Date.now(), i)),
      (n = a(n) && !isNaN(n) ? n : +e.endOf(Date.now(), i) + 1),
      (this.min = Math.min(s, n - 1)),
      (this.max = Math.max(s + 1, n)));
    }

    _getLabelBounds() {
      const t = this.getLabelTimestamps();
      let e = Number.POSITIVE_INFINITY;
      let i = Number.NEGATIVE_INFINITY;
      return (
        t.length && ((e = t[0]), (i = t[t.length - 1])),
        { min: e, max: i }
      );
    }

    buildTicks() {
      const t = this.options;
      const e = t.time;
      const i = t.ticks;
      const s =
          i.source === 'labels' ? this.getLabelTimestamps() : this._generate();
      t.bounds === 'ticks' &&
        s.length &&
        ((this.min = this._userMin || s[0]),
        (this.max = this._userMax || s[s.length - 1]));
      const n = this.min;
      const o = nt(s, n, this.max);
      return (
        (this._unit =
          e.unit ||
          (i.autoSkip ?
            Bo(e.minUnit, this.min, this.max, this._getLabelCapacity(n)) :
            (function(t, e, i, s, n) {
              for (let o = zo.length - 1; o >= zo.indexOf(i); o--) {
                const i = zo[o];
                if (Io[i].common && t._adapter.diff(n, s, i) >= e - 1) {
                  return i;
                }
              }
              return zo[i ? zo.indexOf(i) : 0];
            })(this, o.length, e.minUnit, this.min, this.max))),
        (this._majorUnit =
          i.major.enabled && this._unit !== 'year' ?
            (function(t) {
              for (let e = zo.indexOf(t) + 1, i = zo.length; e < i; ++e) {
                if (Io[zo[e]].common) {
                  return zo[e];
                }
              }
            })(this._unit) :
            void 0),
        this.initOffsets(s),
        t.reverse && o.reverse(),
        No(this, o, this._majorUnit)
      );
    }

    afterAutoSkip() {
      this.options.offsetAfterAutoskip &&
        this.initOffsets(this.ticks.map(t => +t.value));
    }

    initOffsets(t = []) {
      let e;
      let i;
      let s = 0;
      let n = 0;
      this.options.offset &&
        t.length &&
        ((e = this.getDecimalForValue(t[0])),
        (s = t.length === 1 ? 1 - e : (this.getDecimalForValue(t[1]) - e) / 2),
        (i = this.getDecimalForValue(t[t.length - 1])),
        (n =
          t.length === 1 ?
            i :
            (i - this.getDecimalForValue(t[t.length - 2])) / 2));
      const o = t.length < 3 ? 0.5 : 0.25;
      ((s = Z(s, 0, o)),
      (n = Z(n, 0, o)),
      (this._offsets = { start: s, end: n, factor: 1 / (s + 1 + n) }));
    }

    _generate() {
      const t = this._adapter;
      const e = this.min;
      const i = this.max;
      const s = this.options;
      const n = s.time;
      const o = n.unit || Bo(n.minUnit, e, i, this._getLabelCapacity(e));
      const a = l(s.ticks.stepSize, 1);
      const r = o === 'week' && n.isoWeekday;
      const h = N(r) || !0 === r;
      const c = {};
      let d;
      let u;
      let f = e;
      if (
        (h && (f = +t.startOf(f, 'isoWeek', r)),
        (f = +t.startOf(f, h ? 'day' : o)),
        t.diff(i, e, o) > 1e5 * a)
      ) {
        throw new Error(
          `${e
          } and ${
            i
          } are too far apart with stepSize of ${
            a
          } ${
            o}`,
        );
      }
      const g = s.ticks.source === 'data' && this.getDataTimestamps();
      for (d = f, u = 0; d < i; d = +t.add(d, a, o), u++) {
        Wo(c, d, g);
      }
      return (
        (d !== i && s.bounds !== 'ticks' && u !== 1) || Wo(c, d, g),
        Object.keys(c)
          .sort(Fo)
          .map(t => +t)
      );
    }

    getLabelForValue(t) {
      const e = this._adapter;
      const i = this.options.time;
      return i.tooltipFormat ?
        e.format(t, i.tooltipFormat) :
        e.format(t, i.displayFormats.datetime);
    }

    format(t, e) {
      const i = this.options.time.displayFormats;
      const s = this._unit;
      const n = e || i[s];
      return this._adapter.format(t, n);
    }

    _tickFormatFunction(t, e, i, s) {
      const n = this.options;
      const o = n.ticks.callback;
      if (o) {
        return d(o, [t, e, i], this);
      }
      const a = n.time.displayFormats;
      const r = this._unit;
      const l = this._majorUnit;
      const h = r && a[r];
      const c = l && a[l];
      const u = i[e];
      const f = l && c && u && u.major;
      return this._adapter.format(t, s || (f ? c : h));
    }

    generateTickLabels(t) {
      let e, i, s;
      for (e = 0, i = t.length; e < i; ++e) {
        ((s = t[e]), (s.label = this._tickFormatFunction(s.value, e, t)));
      }
    }

    getDecimalForValue(t) {
      return t === null ? NaN : (t - this.min) / (this.max - this.min);
    }

    getPixelForValue(t) {
      const e = this._offsets;
      const i = this.getDecimalForValue(t);
      return this.getPixelForDecimal((e.start + i) * e.factor);
    }

    getValueForPixel(t) {
      const e = this._offsets;
      const i = this.getDecimalForPixel(t) / e.factor - e.end;
      return this.min + i * (this.max - this.min);
    }

    _getLabelSize(t) {
      const e = this.options.ticks;
      const i = this.ctx.measureText(t).width;
      const s = $(this.isHorizontal() ? e.maxRotation : e.minRotation);
      const n = Math.cos(s);
      const o = Math.sin(s);
      const a = this._resolveTickFontOptions(0).size;
      return { w: i * n + a * o, h: i * o + a * n };
    }

    _getLabelCapacity(t) {
      const e = this.options.time;
      const i = e.displayFormats;
      const s = i[e.unit] || i.millisecond;
      const n = this._tickFormatFunction(t, 0, No(this, [t], this._majorUnit), s);
      const o = this._getLabelSize(n);
      const a =
          Math.floor(
            this.isHorizontal() ? this.width / o.w : this.height / o.h,
          ) - 1;
      return a > 0 ? a : 1;
    }

    getDataTimestamps() {
      let t;
      let e;
      let i = this._cache.data || [];
      if (i.length) {
        return i;
      }
      const s = this.getMatchingVisibleMetas();
      if (this._normalized && s.length) {
        return (this._cache.data = s[0].controller.getAllParsedValues(this));
      }
      for (t = 0, e = s.length; t < e; ++t) {
        i = i.concat(s[t].controller.getAllParsedValues(this));
      }
      return (this._cache.data = this.normalize(i));
    }

    getLabelTimestamps() {
      const t = this._cache.labels || [];
      let e, i;
      if (t.length) {
        return t;
      }
      const s = this.getLabels();
      for (e = 0, i = s.length; e < i; ++e) {
        t.push(Vo(this, s[e]));
      }
      return (this._cache.labels = this._normalized ? t : this.normalize(t));
    }

    normalize(t) {
      return lt(t.sort(Fo));
    }
  }
  function jo(t, e, i) {
    let s;
    let n;
    let o;
    let a;
    let r = 0;
    let l = t.length - 1;
    i ?
      (e >= t[r].pos && e <= t[l].pos && ({ lo: r, hi: l } = it(t, 'pos', e)),
      ({ pos: s, time: o } = t[r]),
      ({ pos: n, time: a } = t[l])) :
      (e >= t[r].time &&
          e <= t[l].time &&
          ({ lo: r, hi: l } = it(t, 'time', e)),
      ({ time: s, pos: o } = t[r]),
      ({ time: n, pos: a } = t[l]));
    const h = n - s;
    return h ? o + ((a - o) * (e - s)) / h : o;
  }
  const $o = Object.freeze({
    __proto__: null,
    CategoryScale: class extends tn {
      static id = 'category';
      static defaults = { ticks: { callback: mo } };
      constructor(t) {
        (super(t),
        (this._startValue = void 0),
        (this._valueRange = 0),
        (this._addedLabels = []));
      }

      init(t) {
        const e = this._addedLabels;
        if (e.length) {
          const t = this.getLabels();
          for (const { index: i, label: s } of e) {
            t[i] === s && t.splice(i, 1);
          }
          this._addedLabels = [];
        }
        super.init(t);
      }

      parse(t, e) {
        if (s(t)) {
          return null;
        }
        const i = this.getLabels();
        return ((t, e) => (t === null ? null : Z(Math.round(t), 0, e)))(
          (e =
            isFinite(e) && i[e] === t ?
              e :
              po(i, t, l(e, t), this._addedLabels)),
          i.length - 1,
        );
      }

      determineDataLimits() {
        const { minDefined: t, maxDefined: e } = this.getUserBounds();
        let { min: i, max: s } = this.getMinMax(!0);
        (this.options.bounds === 'ticks' &&
          (t || (i = 0), e || (s = this.getLabels().length - 1)),
        (this.min = i),
        (this.max = s));
      }

      buildTicks() {
        const t = this.min;
        const e = this.max;
        const i = this.options.offset;
        const s = [];
        let n = this.getLabels();
        ((n = t === 0 && e === n.length - 1 ? n : n.slice(t, e + 1)),
        (this._valueRange = Math.max(n.length - (i ? 0 : 1), 1)),
        (this._startValue = this.min - (i ? 0.5 : 0)));
        for (let i = t; i <= e; i++) {
          s.push({ value: i });
        }
        return s;
      }

      getLabelForValue(t) {
        return mo.call(this, t);
      }

      configure() {
        (super.configure(),
        this.isHorizontal() || (this._reversePixels = !this._reversePixels));
      }

      getPixelForValue(t) {
        return (
          typeof t !== 'number' && (t = this.parse(t)),
          t === null ?
            NaN :
            this.getPixelForDecimal((t - this._startValue) / this._valueRange)
        );
      }

      getPixelForTick(t) {
        const e = this.ticks;
        return t < 0 || t > e.length - 1 ?
          null :
          this.getPixelForValue(e[t].value);
      }

      getValueForPixel(t) {
        return Math.round(
          this._startValue + this.getDecimalForPixel(t) * this._valueRange,
        );
      }

      getBasePixel() {
        return this.bottom;
      }
    },
    LinearScale: _o,
    LogarithmicScale: So,
    RadialLinearScale: Ro,
    TimeScale: Ho,
    TimeSeriesScale: class extends Ho {
      static id = 'timeseries';
      static defaults = Ho.defaults;
      constructor(t) {
        (super(t),
        (this._table = []),
        (this._minPos = void 0),
        (this._tableRange = void 0));
      }

      initOffsets() {
        const t = this._getTimestampsForTable();
        const e = (this._table = this.buildLookupTable(t));
        ((this._minPos = jo(e, this.min)),
        (this._tableRange = jo(e, this.max) - this._minPos),
        super.initOffsets(t));
      }

      buildLookupTable(t) {
        const { min: e, max: i } = this;
        const s = [];
        const n = [];
        let o, a, r, l, h;
        for (o = 0, a = t.length; o < a; ++o) {
          ((l = t[o]), l >= e && l <= i && s.push(l));
        }
        if (s.length < 2) {
          return [
            { time: e, pos: 0 },
            { time: i, pos: 1 },
          ];
        }
        for (o = 0, a = s.length; o < a; ++o) {
          ((h = s[o + 1]),
          (r = s[o - 1]),
          (l = s[o]),
          Math.round((h + r) / 2) !== l &&
              n.push({ time: l, pos: o / (a - 1) }));
        }
        return n;
      }

      _generate() {
        const t = this.min;
        const e = this.max;
        const i = super.getDataTimestamps();
        return (
          (i.includes(t) && i.length) || i.splice(0, 0, t),
          (i.includes(e) && i.length !== 1) || i.push(e),
          i.sort((t, e) => t - e)
        );
      }

      _getTimestampsForTable() {
        let t = this._cache.all || [];
        if (t.length) {
          return t;
        }
        const e = this.getDataTimestamps();
        const i = this.getLabelTimestamps();
        return (
          (t =
            e.length && i.length ?
              this.normalize(e.concat(i)) :
              e.length ?
                e :
                i),
          (t = this._cache.all = t),
          t
        );
      }

      getDecimalForValue(t) {
        return (jo(this._table, t) - this._minPos) / this._tableRange;
      }

      getValueForPixel(t) {
        const e = this._offsets;
        const i = this.getDecimalForPixel(t) / e.factor - e.end;
        return jo(this._table, i * this._tableRange + this._minPos, !0);
      }
    },
  });
  const Yo = [
    'rgb(54, 162, 235)',
    'rgb(255, 99, 132)',
    'rgb(255, 159, 64)',
    'rgb(255, 205, 86)',
    'rgb(75, 192, 192)',
    'rgb(153, 102, 255)',
    'rgb(201, 203, 207)',
  ];
  const Uo = Yo.map(t => t.replace('rgb(', 'rgba(').replace(')', ', 0.5)'));
  function Xo(t) {
    return Yo[t % Yo.length];
  }
  function qo(t) {
    return Uo[t % Uo.length];
  }
  function Ko(t) {
    let e = 0;
    return (i, s) => {
      const n = t.getDatasetMeta(s).controller;
      n instanceof $n ?
        (e = (function(t, e) {
          return ((t.backgroundColor = t.data.map(() => Xo(e++))), e);
        })(i, e)) :
        n instanceof Yn ?
          (e = (function(t, e) {
            return ((t.backgroundColor = t.data.map(() => qo(e++))), e);
          })(i, e)) :
          n &&
            (e = (function(t, e) {
              return (
                (t.borderColor = Xo(e)),
                (t.backgroundColor = qo(e)),
                ++e
              );
            })(i, e));
    };
  }
  function Go(t) {
    let e;
    for (e in t) {
      if (t[e].borderColor || t[e].backgroundColor) {
        return !0;
      }
    }
    return !1;
  }
  var Jo = {
    id: 'colors',
    defaults: { enabled: !0, forceOverride: !1 },
    beforeLayout(t, e, i) {
      if (!i.enabled) {
        return;
      }
      const {
        data: { datasets: s },
        options: n,
      } = t.config;
      const { elements: o } = n;
      const a =
          Go(s) ||
          ((r = n) && (r.borderColor || r.backgroundColor)) ||
          (o && Go(o)) ||
          ue.borderColor !== 'rgba(0,0,0,0.1)' ||
          ue.backgroundColor !== 'rgba(0,0,0,0.1)';
      let r;
      if (!i.forceOverride && a) {
        return;
      }
      const l = Ko(t);
      s.forEach(l);
    },
  };
  function Zo(t) {
    if (t._decimated) {
      const e = t._data;
      (delete t._decimated,
      delete t._data,
      Object.defineProperty(t, 'data', {
        configurable: !0,
        enumerable: !0,
        writable: !0,
        value: e,
      }));
    }
  }
  function Qo(t) {
    t.data.datasets.forEach(t => {
      Zo(t);
    });
  }
  var ta = {
    id: 'decimation',
    defaults: { algorithm: 'min-max', enabled: !1 },
    beforeElementsUpdate: (t, e, i) => {
      if (!i.enabled) {
        return void Qo(t);
      }
      const n = t.width;
      t.data.datasets.forEach((e, o) => {
        const { _data: a, indexAxis: r } = e;
        const l = t.getDatasetMeta(o);
        const h = a || e.data;
        if (Pi([r, t.options.indexAxis]) === 'y') {
          return;
        }
        if (!l.controller.supportsDecimation) {
          return;
        }
        const c = t.scales[l.xAxisID];
        if (c.type !== 'linear' && c.type !== 'time') {
          return;
        }
        if (t.options.parsing) {
          return;
        }
        const { start: d, count: u } = (function(t, e) {
          const i = e.length;
          let s;
          let n = 0;
          const { iScale: o } = t;
          const {
            min: a,
            max: r,
            minDefined: l,
            maxDefined: h,
          } = o.getUserBounds();
          return (
            l && (n = Z(it(e, o.axis, a).lo, 0, i - 1)),
            (s = h ? Z(it(e, o.axis, r).hi + 1, n, i) - n : i - n),
            { start: n, count: s }
          );
        })(l, h);
        if (u <= (i.threshold || 4 * n)) {
          return void Zo(e);
        }
        let f;
        switch (
          (s(a) &&
            ((e._data = h),
            delete e.data,
            Object.defineProperty(e, 'data', {
              configurable: !0,
              enumerable: !0,
              get: function() {
                return this._decimated;
              },
              set: function(t) {
                this._data = t;
              },
            })),
          i.algorithm)
        ) {
        case 'lttb':
          f = (function(t, e, i, s, n) {
            const o = n.samples || s;
            if (o >= i) {
              return t.slice(e, e + i);
            }
            const a = [];
            const r = (i - 2) / (o - 2);
            let l = 0;
            const h = e + i - 1;
            let c;
            let d;
            let u;
            let f;
            let g;
            let p = e;
            for (a[l++] = t[p], c = 0; c < o - 2; c++) {
              let s;
              let n = 0;
              let o = 0;
              const h = Math.floor((c + 1) * r) + 1 + e;
              const m = Math.min(Math.floor((c + 2) * r) + 1, i) + e;
              const x = m - h;
              for (s = h; s < m; s++) {
                ((n += t[s].x), (o += t[s].y));
              }
              ((n /= x), (o /= x));
              const b = Math.floor(c * r) + 1 + e;
              const _ = Math.min(Math.floor((c + 1) * r) + 1, i) + e;
              const { x: y, y: v } = t[p];
              for (u = f = -1, s = b; s < _; s++) {
                ((f =
                    0.5 *
                    Math.abs((y - n) * (t[s].y - v) - (y - t[s].x) * (o - v))),
                f > u && ((u = f), (d = t[s]), (g = s)));
              }
              ((a[l++] = d), (p = g));
            }
            return ((a[l++] = t[h]), a);
          })(h, d, u, n, i);
          break;
        case 'min-max':
          f = (function(t, e, i, n) {
            let o;
            let a;
            let r;
            let l;
            let h;
            let c;
            let d;
            let u;
            let f;
            let g;
            let p = 0;
            let m = 0;
            const x = [];
            const b = e + i - 1;
            const _ = t[e].x;
            const y = t[b].x - _;
            for (o = e; o < e + i; ++o) {
              ((a = t[o]), (r = ((a.x - _) / y) * n), (l = a.y));
              const e = 0 | r;
              if (e === h) {
                (l < f ? ((f = l), (c = o)) : l > g && ((g = l), (d = o)),
                (p = (m * p + a.x) / ++m));
              } else {
                const i = o - 1;
                if (!s(c) && !s(d)) {
                  const e = Math.min(c, d);
                  const s = Math.max(c, d);
                  (e !== u && e !== i && x.push({ ...t[e], x: p }),
                  s !== u && s !== i && x.push({ ...t[s], x: p }));
                }
                (o > 0 && i !== u && x.push(t[i]),
                x.push(a),
                (h = e),
                (m = 0),
                (f = g = l),
                (c = d = u = o));
              }
            }
            return x;
          })(h, d, u, n);
          break;
        default:
          throw new Error(
            `Unsupported decimation algorithm '${i.algorithm}'`,
          );
        }
        e._decimated = f;
      });
    },
    destroy(t) {
      Qo(t);
    },
  };
  function ea(t, e, i, s) {
    if (s) {
      return;
    }
    let n = e[t];
    let o = i[t];
    return (
      t === 'angle' && ((n = G(n)), (o = G(o))),
      { property: t, start: n, end: o }
    );
  }
  function ia(t, e, i) {
    for (; e > t; e--) {
      const t = i[e];
      if (!isNaN(t.x) && !isNaN(t.y)) {
        break;
      }
    }
    return e;
  }
  function sa(t, e, i, s) {
    return t && e ? s(t[i], e[i]) : t ? t[i] : e ? e[i] : 0;
  }
  function na(t, e) {
    let i = [];
    let s = !1;
    return (
      n(t) ?
        ((s = !0), (i = t)) :
        (i = (function(t, e) {
          const { x: i = null, y: s = null } = t || {};
          const n = e.points;
          const o = [];
          return (
            e.segments.forEach(({ start: t, end: e }) => {
              e = ia(t, e, n);
              const a = n[t];
              const r = n[e];
              s !== null ?
                (o.push({ x: a.x, y: s }), o.push({ x: r.x, y: s })) :
                i !== null &&
                    (o.push({ x: i, y: a.y }), o.push({ x: i, y: r.y }));
            }),
            o
          );
        })(t, e)),
      i.length ?
        new oo({ points: i, options: { tension: 0 }, _loop: s, _fullLoop: s }) :
        null
    );
  }
  function oa(t) {
    return t && !1 !== t.fill;
  }
  function aa(t, e, i) {
    let s = t[e].fill;
    const n = [e];
    let o;
    if (!i) {
      return s;
    }
    for (; !1 !== s && n.indexOf(s) === -1;) {
      if (!a(s)) {
        return s;
      }
      if (((o = t[s]), !o)) {
        return !1;
      }
      if (o.visible) {
        return s;
      }
      (n.push(s), (s = o.fill));
    }
    return !1;
  }
  function ra(t, e, i) {
    const s = (function(t) {
      const e = t.options;
      const i = e.fill;
      let s = l(i && i.target, i);
      void 0 === s && (s = !!e.backgroundColor);
      if (!1 === s || s === null) {
        return !1;
      }
      if (!0 === s) {
        return 'origin';
      }
      return s;
    })(t);
    if (o(s)) {
      return !isNaN(s.value) && s;
    }
    const n = parseFloat(s);
    return a(n) && Math.floor(n) === n ?
      (function(t, e, i, s) {
        (t !== '-' && t !== '+') || (i = e + i);
        if (i === e || i < 0 || i >= s) {
          return !1;
        }
        return i;
      })(s[0], e, n, i) :
      ['origin', 'start', 'end', 'stack', 'shape'].indexOf(s) >= 0 && s;
  }
  function la(t, e, i) {
    const s = [];
    for (let n = 0; n < i.length; n++) {
      const o = i[n];
      const { first: a, last: r, point: l } = ha(o, e, 'x');
      if (!(!l || (a && r))) {
        if (a) {
          s.unshift(l);
        } else if ((t.push(l), !r)) {
          break;
        }
      }
    }
    t.push(...s);
  }
  function ha(t, e, i) {
    const s = t.interpolate(e, i);
    if (!s) {
      return {};
    }
    const n = s[i];
    const o = t.segments;
    const a = t.points;
    let r = !1;
    let l = !1;
    for (let t = 0; t < o.length; t++) {
      const e = o[t];
      const s = a[e.start][i];
      const h = a[e.end][i];
      if (tt(n, s, h)) {
        ((r = n === s), (l = n === h));
        break;
      }
    }
    return { first: r, last: l, point: s };
  }
  class ca {
    constructor(t) {
      ((this.x = t.x), (this.y = t.y), (this.radius = t.radius));
    }

    pathSegment(t, e, i) {
      const { x: s, y: n, radius: o } = this;
      return (
        (e = e || { start: 0, end: O }),
        t.arc(s, n, o, e.end, e.start, !0),
        !i.bounds
      );
    }

    interpolate(t) {
      const { x: e, y: i, radius: s } = this;
      const n = t.angle;
      return { x: e + Math.cos(n) * s, y: i + Math.sin(n) * s, angle: n };
    }
  }
  function da(t) {
    const { chart: e, fill: i, line: s } = t;
    if (a(i)) {
      return (function(t, e) {
        const i = t.getDatasetMeta(e);
        const s = i && t.isDatasetVisible(e);
        return s ? i.dataset : null;
      })(e, i);
    }
    if (i === 'stack') {
      return (function(t) {
        const { scale: e, index: i, line: s } = t;
        const n = [];
        const o = s.segments;
        const a = s.points;
        const r = (function(t, e) {
          const i = [];
          const s = t.getMatchingVisibleMetas('line');
          for (let t = 0; t < s.length; t++) {
            const n = s[t];
            if (n.index === e) {
              break;
            }
            n.hidden || i.unshift(n.dataset);
          }
          return i;
        })(e, i);
        r.push(na({ x: null, y: e.bottom }, s));
        for (let t = 0; t < o.length; t++) {
          const e = o[t];
          for (let t = e.start; t <= e.end; t++) {
            la(n, a[t], r);
          }
        }
        return new oo({ points: n, options: {} });
      })(t);
    }
    if (i === 'shape') {
      return !0;
    }
    const n = (function(t) {
      const e = t.scale || {};
      if (e.getPointPositionForValue) {
        return (function(t) {
          const { scale: e, fill: i } = t;
          const s = e.options;
          const n = e.getLabels().length;
          const a = s.reverse ? e.max : e.min;
          const r = (function(t, e, i) {
            let s;
            return (
              (s =
                  t === 'start' ?
                    i :
                    t === 'end' ?
                      e.options.reverse ?
                        e.min :
                        e.max :
                      o(t) ?
                        t.value :
                        e.getBaseValue()),
              s
            );
          })(i, e, a);
          const l = [];
          if (s.grid.circular) {
            const t = e.getPointPositionForValue(0, a);
            return new ca({
              x: t.x,
              y: t.y,
              radius: e.getDistanceFromCenterForValue(r),
            });
          }
          for (let t = 0; t < n; ++t) {
            l.push(e.getPointPositionForValue(t, r));
          }
          return l;
        })(t);
      }
      return (function(t) {
        const { scale: e = {}, fill: i } = t;
        const s = (function(t, e) {
          let i = null;
          return (
            t === 'start' ?
              (i = e.bottom) :
              t === 'end' ?
                (i = e.top) :
                o(t) ?
                  (i = e.getPixelForValue(t.value)) :
                  e.getBasePixel && (i = e.getBasePixel()),
            i
          );
        })(i, e);
        if (a(s)) {
          const t = e.isHorizontal();
          return { x: t ? s : null, y: t ? null : s };
        }
        return null;
      })(t);
    })(t);
    return n instanceof ca ? n : na(n, s);
  }
  function ua(t, e, i) {
    const s = da(e);
    const { chart: n, index: o, line: a, scale: r, axis: l } = e;
    const h = a.options;
    const c = h.fill;
    const d = h.backgroundColor;
    const { above: u = d, below: f = d } = c || {};
    const g = n.getDatasetMeta(o);
    const p = Ni(n, g);
    s &&
      a.points.length &&
      (Ie(t, i),
      (function(t, e) {
        const {
          line: i,
          target: s,
          above: n,
          below: o,
          area: a,
          scale: r,
          clip: l,
        } = e;
        const h = i._loop ? 'angle' : e.axis;
        t.save();
        let c = o;
        o !== n &&
          (h === 'x' ?
            (fa(t, s, a.top),
            pa(t, {
              line: i,
              target: s,
              color: n,
              scale: r,
              property: h,
              clip: l,
            }),
            t.restore(),
            t.save(),
            fa(t, s, a.bottom)) :
            h === 'y' &&
              (ga(t, s, a.left),
              pa(t, {
                line: i,
                target: s,
                color: o,
                scale: r,
                property: h,
                clip: l,
              }),
              t.restore(),
              t.save(),
              ga(t, s, a.right),
              (c = n)));
        (pa(t, {
          line: i,
          target: s,
          color: c,
          scale: r,
          property: h,
          clip: l,
        }),
        t.restore());
      })(t, {
        line: a,
        target: s,
        above: u,
        below: f,
        area: i,
        scale: r,
        axis: l,
        clip: p,
      }),
      ze(t));
  }
  function fa(t, e, i) {
    const { segments: s, points: n } = e;
    let o = !0;
    let a = !1;
    t.beginPath();
    for (const r of s) {
      const { start: s, end: l } = r;
      const h = n[s];
      const c = n[ia(s, l, n)];
      (o ?
        (t.moveTo(h.x, h.y), (o = !1)) :
        (t.lineTo(h.x, i), t.lineTo(h.x, h.y)),
      (a = !!e.pathSegment(t, r, { move: a })),
      a ? t.closePath() : t.lineTo(c.x, i));
    }
    (t.lineTo(e.first().x, i), t.closePath(), t.clip());
  }
  function ga(t, e, i) {
    const { segments: s, points: n } = e;
    let o = !0;
    let a = !1;
    t.beginPath();
    for (const r of s) {
      const { start: s, end: l } = r;
      const h = n[s];
      const c = n[ia(s, l, n)];
      (o ?
        (t.moveTo(h.x, h.y), (o = !1)) :
        (t.lineTo(i, h.y), t.lineTo(h.x, h.y)),
      (a = !!e.pathSegment(t, r, { move: a })),
      a ? t.closePath() : t.lineTo(i, c.y));
    }
    (t.lineTo(i, e.first().y), t.closePath(), t.clip());
  }
  function pa(t, e) {
    const { line: i, target: s, property: n, color: o, scale: a, clip: r } = e;
    const l = (function(t, e, i) {
      const s = t.segments;
      const n = t.points;
      const o = e.points;
      const a = [];
      for (const t of s) {
        let { start: s, end: r } = t;
        r = ia(s, r, n);
        const l = ea(i, n[s], n[r], t.loop);
        if (!e.segments) {
          a.push({ source: t, target: l, start: n[s], end: n[r] });
          continue;
        }
        const h = Ii(e, l);
        for (const e of h) {
          const s = ea(i, o[e.start], o[e.end], e.loop);
          const r = Ri(t, n, s);
          for (const t of r) {
            a.push({
              source: t,
              target: e,
              start: { [i]: sa(l, s, 'start', Math.max) },
              end: { [i]: sa(l, s, 'end', Math.min) },
            });
          }
        }
      }
      return a;
    })(i, s, n);
    for (const { source: e, target: h, start: c, end: d } of l) {
      const { style: { backgroundColor: l = o } = {} } = e;
      const u = !0 !== s;
      (t.save(),
      (t.fillStyle = l),
      ma(t, a, r, u && ea(n, c, d)),
      t.beginPath());
      const f = !!i.pathSegment(t, e);
      let g;
      if (u) {
        f ? t.closePath() : xa(t, s, d, n);
        const e = !!s.pathSegment(t, h, { move: f, reverse: !0 });
        ((g = f && e), g || xa(t, s, c, n));
      }
      (t.closePath(), t.fill(g ? 'evenodd' : 'nonzero'), t.restore());
    }
  }
  function ma(t, e, i, s) {
    const n = e.chart.chartArea;
    const { property: o, start: a, end: r } = s || {};
    if (o === 'x' || o === 'y') {
      let e, s, l, h;
      (o === 'x' ?
        ((e = a), (s = n.top), (l = r), (h = n.bottom)) :
        ((e = n.left), (s = a), (l = n.right), (h = r)),
      t.beginPath(),
      i &&
          ((e = Math.max(e, i.left)),
          (l = Math.min(l, i.right)),
          (s = Math.max(s, i.top)),
          (h = Math.min(h, i.bottom))),
      t.rect(e, s, l - e, h - s),
      t.clip());
    }
  }
  function xa(t, e, i, s) {
    const n = e.interpolate(i, s);
    n && t.lineTo(n.x, n.y);
  }
  var ba = {
    id: 'filler',
    afterDatasetsUpdate(t, e, i) {
      const s = (t.data.datasets || []).length;
      const n = [];
      let o, a, r, l;
      for (a = 0; a < s; ++a) {
        ((o = t.getDatasetMeta(a)),
        (r = o.dataset),
        (l = null),
        r &&
            r.options &&
            r instanceof oo &&
            (l = {
              visible: t.isDatasetVisible(a),
              index: a,
              fill: ra(r, a, s),
              chart: t,
              axis: o.controller.options.indexAxis,
              scale: o.vScale,
              line: r,
            }),
        (o.$filler = l),
        n.push(l));
      }
      for (a = 0; a < s; ++a) {
        ((l = n[a]), l && !1 !== l.fill && (l.fill = aa(n, a, i.propagate)));
      }
    },
    beforeDraw(t, e, i) {
      const s = i.drawTime === 'beforeDraw';
      const n = t.getSortedVisibleDatasetMetas();
      const o = t.chartArea;
      for (let e = n.length - 1; e >= 0; --e) {
        const i = n[e].$filler;
        i &&
          (i.line.updateControlPoints(o, i.axis),
          s && i.fill && ua(t.ctx, i, o));
      }
    },
    beforeDatasetsDraw(t, e, i) {
      if (i.drawTime !== 'beforeDatasetsDraw') {
        return;
      }
      const s = t.getSortedVisibleDatasetMetas();
      for (let e = s.length - 1; e >= 0; --e) {
        const i = s[e].$filler;
        oa(i) && ua(t.ctx, i, t.chartArea);
      }
    },
    beforeDatasetDraw(t, e, i) {
      const s = e.meta.$filler;
      oa(s) && i.drawTime === 'beforeDatasetDraw' && ua(t.ctx, s, t.chartArea);
    },
    defaults: { propagate: !0, drawTime: 'beforeDatasetDraw' },
  };
  const _a = (t, e) => {
    let { boxHeight: i = e, boxWidth: s = e } = t;
    return (
      t.usePointStyle &&
        ((i = Math.min(i, e)), (s = t.pointStyleWidth || Math.min(s, e))),
      { boxWidth: s, boxHeight: i, itemHeight: Math.max(e, i) }
    );
  };
  class ya extends $s {
    constructor(t) {
      (super(),
      (this._added = !1),
      (this.legendHitBoxes = []),
      (this._hoveredItem = null),
      (this.doughnutMode = !1),
      (this.chart = t.chart),
      (this.options = t.options),
      (this.ctx = t.ctx),
      (this.legendItems = void 0),
      (this.columnSizes = void 0),
      (this.lineWidths = void 0),
      (this.maxHeight = void 0),
      (this.maxWidth = void 0),
      (this.top = void 0),
      (this.bottom = void 0),
      (this.left = void 0),
      (this.right = void 0),
      (this.height = void 0),
      (this.width = void 0),
      (this._margins = void 0),
      (this.position = void 0),
      (this.weight = void 0),
      (this.fullSize = void 0));
    }

    update(t, e, i) {
      ((this.maxWidth = t),
      (this.maxHeight = e),
      (this._margins = i),
      this.setDimensions(),
      this.buildLabels(),
      this.fit());
    }

    setDimensions() {
      this.isHorizontal() ?
        ((this.width = this.maxWidth),
        (this.left = this._margins.left),
        (this.right = this.width)) :
        ((this.height = this.maxHeight),
        (this.top = this._margins.top),
        (this.bottom = this.height));
    }

    buildLabels() {
      const t = this.options.labels || {};
      let e = d(t.generateLabels, [this.chart], this) || [];
      (t.filter && (e = e.filter(e => t.filter(e, this.chart.data))),
      t.sort && (e = e.sort((e, i) => t.sort(e, i, this.chart.data))),
      this.options.reverse && e.reverse(),
      (this.legendItems = e));
    }

    fit() {
      const { options: t, ctx: e } = this;
      if (!t.display) {
        return void (this.width = this.height = 0);
      }
      const i = t.labels;
      const s = Si(i.font);
      const n = s.size;
      const o = this._computeTitleHeight();
      const { boxWidth: a, itemHeight: r } = _a(i, n);
      let l, h;
      ((e.font = s.string),
      this.isHorizontal() ?
        ((l = this.maxWidth), (h = this._fitRows(o, n, a, r) + 10)) :
        ((h = this.maxHeight), (l = this._fitCols(o, s, a, r) + 10)),
      (this.width = Math.min(l, t.maxWidth || this.maxWidth)),
      (this.height = Math.min(h, t.maxHeight || this.maxHeight)));
    }

    _fitRows(t, e, i, s) {
      const {
        ctx: n,
        maxWidth: o,
        options: {
          labels: { padding: a },
        },
      } = this;
      const r = (this.legendHitBoxes = []);
      const l = (this.lineWidths = [0]);
      const h = s + a;
      let c = t;
      ((n.textAlign = 'left'), (n.textBaseline = 'middle'));
      let d = -1;
      let u = -h;
      return (
        this.legendItems.forEach((t, f) => {
          const g = i + e / 2 + n.measureText(t.text).width;
          ((f === 0 || l[l.length - 1] + g + 2 * a > o) &&
            ((c += h), (l[l.length - (f > 0 ? 0 : 1)] = 0), (u += h), d++),
          (r[f] = { left: 0, top: u, row: d, width: g, height: s }),
          (l[l.length - 1] += g + a));
        }),
        c
      );
    }

    _fitCols(t, e, i, s) {
      const {
        ctx: n,
        maxHeight: o,
        options: {
          labels: { padding: a },
        },
      } = this;
      const r = (this.legendHitBoxes = []);
      const l = (this.columnSizes = []);
      const h = o - t;
      let c = a;
      let d = 0;
      let u = 0;
      let f = 0;
      let g = 0;
      return (
        this.legendItems.forEach((t, o) => {
          const { itemWidth: p, itemHeight: m } = (function(t, e, i, s, n) {
            const o = (function(t, e, i, s) {
              let n = t.text;
              n &&
                  typeof n !== 'string' &&
                  (n = n.reduce((t, e) => (t.length > e.length ? t : e)));
              return e + i.size / 2 + s.measureText(n).width;
            })(s, t, e, i);
            const a = (function(t, e, i) {
              let s = t;
              typeof e.text !== 'string' && (s = va(e, i));
              return s;
            })(n, s, e.lineHeight);
            return { itemWidth: o, itemHeight: a };
          })(i, e, n, t, s);
          (o > 0 &&
            u + m + 2 * a > h &&
            ((c += d + a),
            l.push({ width: d, height: u }),
            (f += d + a),
            g++,
            (d = u = 0)),
          (r[o] = { left: f, top: u, col: g, width: p, height: m }),
          (d = Math.max(d, p)),
          (u += m + a));
        }),
        (c += d),
        l.push({ width: d, height: u }),
        c
      );
    }

    adjustHitBoxes() {
      if (!this.options.display) {
        return;
      }
      const t = this._computeTitleHeight();
      const {
        legendHitBoxes: e,
        options: {
          align: i,
          labels: { padding: s },
          rtl: n,
        },
      } = this;
      const o = Oi(n, this.left, this.width);
      if (this.isHorizontal()) {
        let n = 0;
        let a = ft(i, this.left + s, this.right - this.lineWidths[n]);
        for (const r of e) {
          (n !== r.row &&
            ((n = r.row),
            (a = ft(i, this.left + s, this.right - this.lineWidths[n]))),
          (r.top += this.top + t + s),
          (r.left = o.leftForLtr(o.x(a), r.width)),
          (a += r.width + s));
        }
      } else {
        let n = 0;
        let a = ft(i, this.top + t + s, this.bottom - this.columnSizes[n].height);
        for (const r of e) {
          (r.col !== n &&
            ((n = r.col),
            (a = ft(
              i,
              this.top + t + s,
              this.bottom - this.columnSizes[n].height,
            ))),
          (r.top = a),
          (r.left += this.left + s),
          (r.left = o.leftForLtr(o.x(r.left), r.width)),
          (a += r.height + s));
        }
      }
    }

    isHorizontal() {
      return (
        this.options.position === 'top' || this.options.position === 'bottom'
      );
    }

    draw() {
      if (this.options.display) {
        const t = this.ctx;
        (Ie(t, this), this._draw(), ze(t));
      }
    }

    _draw() {
      const { options: t, columnSizes: e, lineWidths: i, ctx: s } = this;
      const { align: n, labels: o } = t;
      const a = ue.color;
      const r = Oi(t.rtl, this.left, this.width);
      const h = Si(o.font);
      const { padding: c } = o;
      const d = h.size;
      const u = d / 2;
      let f;
      (this.drawTitle(),
      (s.textAlign = r.textAlign('left')),
      (s.textBaseline = 'middle'),
      (s.lineWidth = 0.5),
      (s.font = h.string));
      const { boxWidth: g, boxHeight: p, itemHeight: m } = _a(o, d);
      const x = this.isHorizontal();
      const b = this._computeTitleHeight();
      ((f = x ?
        {
          x: ft(n, this.left + c, this.right - i[0]),
          y: this.top + c + b,
          line: 0,
        } :
        {
          x: this.left + c,
          y: ft(n, this.top + b + c, this.bottom - e[0].height),
          line: 0,
        }),
      Ai(this.ctx, t.textDirection));
      const _ = m + c;
      (this.legendItems.forEach((y, v) => {
        ((s.strokeStyle = y.fontColor), (s.fillStyle = y.fontColor));
        const M = s.measureText(y.text).width;
        const w = r.textAlign(y.textAlign || (y.textAlign = o.textAlign));
        const k = g + u + M;
        let S = f.x;
        let P = f.y;
        (r.setWidth(this.width),
        x ?
          v > 0 &&
              S + k + c > this.right &&
              ((P = f.y += _),
              f.line++,
              (S = f.x = ft(n, this.left + c, this.right - i[f.line]))) :
          v > 0 &&
              P + _ > this.bottom &&
              ((S = f.x = S + e[f.line].width + c),
              f.line++,
              (P = f.y =
                ft(n, this.top + b + c, this.bottom - e[f.line].height))));
        if (
          ((function(t, e, i) {
            if (isNaN(g) || g <= 0 || isNaN(p) || p < 0) {
              return;
            }
            s.save();
            const n = l(i.lineWidth, 1);
            if (
              ((s.fillStyle = l(i.fillStyle, a)),
              (s.lineCap = l(i.lineCap, 'butt')),
              (s.lineDashOffset = l(i.lineDashOffset, 0)),
              (s.lineJoin = l(i.lineJoin, 'miter')),
              (s.lineWidth = n),
              (s.strokeStyle = l(i.strokeStyle, a)),
              s.setLineDash(l(i.lineDash, [])),
              o.usePointStyle)
            ) {
              const a = {
                radius: (p * Math.SQRT2) / 2,
                pointStyle: i.pointStyle,
                rotation: i.rotation,
                borderWidth: n,
              };
              const l = r.xPlus(t, g / 2);
              Ee(s, a, l, e + u, o.pointStyleWidth && g);
            } else {
              const o = e + Math.max((d - p) / 2, 0);
              const a = r.leftForLtr(t, g);
              const l = wi(i.borderRadius);
              (s.beginPath(),
              Object.values(l).some(t => t !== 0) ?
                He(s, { x: a, y: o, w: g, h: p, radius: l }) :
                s.rect(a, o, g, p),
              s.fill(),
              n !== 0 && s.stroke());
            }
            s.restore();
          })(r.x(S), P, y),
          (S = gt(w, S + g + u, x ? S + k : this.right, t.rtl)),
          (function(t, e, i) {
            Ne(s, i.text, t, e + m / 2, h, {
              strikethrough: i.hidden,
              textAlign: r.textAlign(i.textAlign),
            });
          })(r.x(S), P, y),
          x)
        ) {
          f.x += k + c;
        } else if (typeof y.text !== 'string') {
          const t = h.lineHeight;
          f.y += va(y, t) + c;
        } else {
          f.y += _;
        }
      }),
      Ti(this.ctx, t.textDirection));
    }

    drawTitle() {
      const t = this.options;
      const e = t.title;
      const i = Si(e.font);
      const s = ki(e.padding);
      if (!e.display) {
        return;
      }
      const n = Oi(t.rtl, this.left, this.width);
      const o = this.ctx;
      const a = e.position;
      const r = i.size / 2;
      const l = s.top + r;
      let h;
      let c = this.left;
      let d = this.width;
      if (this.isHorizontal()) {
        ((d = Math.max(...this.lineWidths)),
        (h = this.top + l),
        (c = ft(t.align, c, this.right - d)));
      } else {
        const e = this.columnSizes.reduce((t, e) => Math.max(t, e.height), 0);
        h =
          l +
          ft(
            t.align,
            this.top,
            this.bottom - e - t.labels.padding - this._computeTitleHeight(),
          );
      }
      const u = ft(a, c, c + d);
      ((o.textAlign = n.textAlign(ut(a))),
      (o.textBaseline = 'middle'),
      (o.strokeStyle = e.color),
      (o.fillStyle = e.color),
      (o.font = i.string),
      Ne(o, e.text, u, h, i));
    }

    _computeTitleHeight() {
      const t = this.options.title;
      const e = Si(t.font);
      const i = ki(t.padding);
      return t.display ? e.lineHeight + i.height : 0;
    }

    _getLegendItemAt(t, e) {
      let i, s, n;
      if (tt(t, this.left, this.right) && tt(e, this.top, this.bottom)) {
        for (n = this.legendHitBoxes, i = 0; i < n.length; ++i) {
          if (
            ((s = n[i]),
            tt(t, s.left, s.left + s.width) && tt(e, s.top, s.top + s.height))
          ) {
            return this.legendItems[i];
          }
        }
      }
      return null;
    }

    handleEvent(t) {
      const e = this.options;
      if (
        !(function(t, e) {
          if (
            (t === 'mousemove' || t === 'mouseout') &&
            (e.onHover || e.onLeave)
          ) {
            return !0;
          }
          if (e.onClick && (t === 'click' || t === 'mouseup')) {
            return !0;
          }
          return !1;
        })(t.type, e)
      ) {
        return;
      }
      const i = this._getLegendItemAt(t.x, t.y);
      if (t.type === 'mousemove' || t.type === 'mouseout') {
        const o = this._hoveredItem;
        const a =
            ((n = i),
            (s = o) !== null &&
              n !== null &&
              s.datasetIndex === n.datasetIndex &&
              s.index === n.index);
        (o && !a && d(e.onLeave, [t, o, this], this),
        (this._hoveredItem = i),
        i && !a && d(e.onHover, [t, i, this], this));
      } else {
        i && d(e.onClick, [t, i, this], this);
      }
      let s, n;
    }
  }
  function va(t, e) {
    return e * (t.text ? t.text.length : 0);
  }
  var Ma = {
    id: 'legend',
    _element: ya,
    start(t, e, i) {
      const s = (t.legend = new ya({ ctx: t.ctx, options: i, chart: t }));
      (ls.configure(t, s, i), ls.addBox(t, s));
    },
    stop(t) {
      (ls.removeBox(t, t.legend), delete t.legend);
    },
    beforeUpdate(t, e, i) {
      const s = t.legend;
      (ls.configure(t, s, i), (s.options = i));
    },
    afterUpdate(t) {
      const e = t.legend;
      (e.buildLabels(), e.adjustHitBoxes());
    },
    afterEvent(t, e) {
      e.replay || t.legend.handleEvent(e.event);
    },
    defaults: {
      display: !0,
      position: 'top',
      align: 'center',
      fullSize: !0,
      reverse: !1,
      weight: 1e3,
      onClick(t, e, i) {
        const s = e.datasetIndex;
        const n = i.chart;
        n.isDatasetVisible(s) ?
          (n.hide(s), (e.hidden = !0)) :
          (n.show(s), (e.hidden = !1));
      },
      onHover: null,
      onLeave: null,
      labels: {
        color: t => t.chart.options.color,
        boxWidth: 40,
        padding: 10,
        generateLabels(t) {
          const e = t.data.datasets;
          const {
            labels: {
              usePointStyle: i,
              pointStyle: s,
              textAlign: n,
              color: o,
              useBorderRadius: a,
              borderRadius: r,
            },
          } = t.legend.options;
          return t._getSortedDatasetMetas().map(t => {
            const l = t.controller.getStyle(i ? 0 : void 0);
            const h = ki(l.borderWidth);
            return {
              text: e[t.index].label,
              fillStyle: l.backgroundColor,
              fontColor: o,
              hidden: !t.visible,
              lineCap: l.borderCapStyle,
              lineDash: l.borderDash,
              lineDashOffset: l.borderDashOffset,
              lineJoin: l.borderJoinStyle,
              lineWidth: (h.width + h.height) / 4,
              strokeStyle: l.borderColor,
              pointStyle: s || l.pointStyle,
              rotation: l.rotation,
              textAlign: n || l.textAlign,
              borderRadius: a && (r || l.borderRadius),
              datasetIndex: t.index,
            };
          }, this);
        },
      },
      title: {
        color: t => t.chart.options.color,
        display: !1,
        position: 'center',
        text: '',
      },
    },
    descriptors: {
      _scriptable: t => !t.startsWith('on'),
      labels: {
        _scriptable: t => !['generateLabels', 'filter', 'sort'].includes(t),
      },
    },
  };
  class wa extends $s {
    constructor(t) {
      (super(),
      (this.chart = t.chart),
      (this.options = t.options),
      (this.ctx = t.ctx),
      (this._padding = void 0),
      (this.top = void 0),
      (this.bottom = void 0),
      (this.left = void 0),
      (this.right = void 0),
      (this.width = void 0),
      (this.height = void 0),
      (this.position = void 0),
      (this.weight = void 0),
      (this.fullSize = void 0));
    }

    update(t, e) {
      const i = this.options;
      if (((this.left = 0), (this.top = 0), !i.display)) {
        return void (this.width = this.height = this.right = this.bottom = 0);
      }
      ((this.width = this.right = t), (this.height = this.bottom = e));
      const s = n(i.text) ? i.text.length : 1;
      this._padding = ki(i.padding);
      const o = s * Si(i.font).lineHeight + this._padding.height;
      this.isHorizontal() ? (this.height = o) : (this.width = o);
    }

    isHorizontal() {
      const t = this.options.position;
      return t === 'top' || t === 'bottom';
    }

    _drawArgs(t) {
      const { top: e, left: i, bottom: s, right: n, options: o } = this;
      const a = o.align;
      let r;
      let l;
      let h;
      let c = 0;
      return (
        this.isHorizontal() ?
          ((l = ft(a, i, n)), (h = e + t), (r = n - i)) :
          (o.position === 'left' ?
            ((l = i + t), (h = ft(a, s, e)), (c = -0.5 * C)) :
            ((l = n - t), (h = ft(a, e, s)), (c = 0.5 * C)),
          (r = s - e)),
        { titleX: l, titleY: h, maxWidth: r, rotation: c }
      );
    }

    draw() {
      const t = this.ctx;
      const e = this.options;
      if (!e.display) {
        return;
      }
      const i = Si(e.font);
      const s = i.lineHeight / 2 + this._padding.top;
      const { titleX: n, titleY: o, maxWidth: a, rotation: r } = this._drawArgs(s);
      Ne(t, e.text, 0, 0, i, {
        color: e.color,
        maxWidth: a,
        rotation: r,
        textAlign: ut(e.align),
        textBaseline: 'middle',
        translation: [n, o],
      });
    }
  }
  var ka = {
    id: 'title',
    _element: wa,
    start(t, e, i) {
      !(function(t, e) {
        const i = new wa({ ctx: t.ctx, options: e, chart: t });
        (ls.configure(t, i, e), ls.addBox(t, i), (t.titleBlock = i));
      })(t, i);
    },
    stop(t) {
      const e = t.titleBlock;
      (ls.removeBox(t, e), delete t.titleBlock);
    },
    beforeUpdate(t, e, i) {
      const s = t.titleBlock;
      (ls.configure(t, s, i), (s.options = i));
    },
    defaults: {
      align: 'center',
      display: !1,
      font: { weight: 'bold' },
      fullSize: !0,
      padding: 10,
      position: 'top',
      text: '',
      weight: 2e3,
    },
    defaultRoutes: { color: 'color' },
    descriptors: { _scriptable: !0, _indexable: !1 },
  };
  const Sa = new WeakMap();
  var Pa = {
    id: 'subtitle',
    start(t, e, i) {
      const s = new wa({ ctx: t.ctx, options: i, chart: t });
      (ls.configure(t, s, i), ls.addBox(t, s), Sa.set(t, s));
    },
    stop(t) {
      (ls.removeBox(t, Sa.get(t)), Sa.delete(t));
    },
    beforeUpdate(t, e, i) {
      const s = Sa.get(t);
      (ls.configure(t, s, i), (s.options = i));
    },
    defaults: {
      align: 'center',
      display: !1,
      font: { weight: 'normal' },
      fullSize: !0,
      padding: 0,
      position: 'top',
      text: '',
      weight: 1500,
    },
    defaultRoutes: { color: 'color' },
    descriptors: { _scriptable: !0, _indexable: !1 },
  };
  const Da = {
    average(t) {
      if (!t.length) {
        return !1;
      }
      let e;
      let i;
      const s = new Set();
      let n = 0;
      let o = 0;
      for (e = 0, i = t.length; e < i; ++e) {
        const i = t[e].element;
        if (i && i.hasValue()) {
          const t = i.tooltipPosition();
          (s.add(t.x), (n += t.y), ++o);
        }
      }
      if (o === 0 || s.size === 0) {
        return !1;
      }
      return { x: [...s].reduce((t, e) => t + e) / s.size, y: n / o };
    },
    nearest(t, e) {
      if (!t.length) {
        return !1;
      }
      let i;
      let s;
      let n;
      let o = e.x;
      let a = e.y;
      let r = Number.POSITIVE_INFINITY;
      for (i = 0, s = t.length; i < s; ++i) {
        const s = t[i].element;
        if (s && s.hasValue()) {
          const t = q(e, s.getCenterPoint());
          t < r && ((r = t), (n = s));
        }
      }
      if (n) {
        const t = n.tooltipPosition();
        ((o = t.x), (a = t.y));
      }
      return { x: o, y: a };
    },
  };
  function Ca(t, e) {
    return (e && (n(e) ? Array.prototype.push.apply(t, e) : t.push(e)), t);
  }
  function Oa(t) {
    return (typeof t === 'string' || t instanceof String) && t.indexOf('\n') > -1 ?
      t.split('\n') :
      t;
  }
  function Aa(t, e) {
    const { element: i, datasetIndex: s, index: n } = e;
    const o = t.getDatasetMeta(s).controller;
    const { label: a, value: r } = o.getLabelAndValue(n);
    return {
      chart: t,
      label: a,
      parsed: o.getParsed(n),
      raw: t.data.datasets[s].data[n],
      formattedValue: r,
      dataset: o.getDataset(),
      dataIndex: n,
      datasetIndex: s,
      element: i,
    };
  }
  function Ta(t, e) {
    const i = t.chart.ctx;
    const { body: s, footer: n, title: o } = t;
    const { boxWidth: a, boxHeight: r } = e;
    const l = Si(e.bodyFont);
    const h = Si(e.titleFont);
    const c = Si(e.footerFont);
    const d = o.length;
    const f = n.length;
    const g = s.length;
    const p = ki(e.padding);
    let m = p.height;
    let x = 0;
    let b = s.reduce(
      (t, e) => t + e.before.length + e.lines.length + e.after.length,
      0,
    );
    if (
      ((b += t.beforeBody.length + t.afterBody.length),
      d &&
        (m +=
          d * h.lineHeight + (d - 1) * e.titleSpacing + e.titleMarginBottom),
      b)
    ) {
      m +=
        g * (e.displayColors ? Math.max(r, l.lineHeight) : l.lineHeight) +
        (b - g) * l.lineHeight +
        (b - 1) * e.bodySpacing;
    }
    f &&
      (m += e.footerMarginTop + f * c.lineHeight + (f - 1) * e.footerSpacing);
    let _ = 0;
    const y = function(t) {
      x = Math.max(x, i.measureText(t).width + _);
    };
    return (
      i.save(),
      (i.font = h.string),
      u(t.title, y),
      (i.font = l.string),
      u(t.beforeBody.concat(t.afterBody), y),
      (_ = e.displayColors ? a + 2 + e.boxPadding : 0),
      u(s, t => {
        (u(t.before, y), u(t.lines, y), u(t.after, y));
      }),
      (_ = 0),
      (i.font = c.string),
      u(t.footer, y),
      i.restore(),
      (x += p.width),
      { width: x, height: m }
    );
  }
  function La(t, e, i, s) {
    const { x: n, width: o } = i;
    const {
      width: a,
      chartArea: { left: r, right: l },
    } = t;
    let h = 'center';
    return (
      s === 'center' ?
        (h = n <= (r + l) / 2 ? 'left' : 'right') :
        n <= o / 2 ?
          (h = 'left') :
          n >= a - o / 2 && (h = 'right'),
      (function(t, e, i, s) {
        const { x: n, width: o } = s;
        const a = i.caretSize + i.caretPadding;
        return (
          (t === 'left' && n + o + a > e.width) ||
          (t === 'right' && n - o - a < 0) ||
          void 0
        );
      })(h, t, e, i) && (h = 'center'),
      h
    );
  }
  function Ea(t, e, i) {
    const s =
      i.yAlign ||
      e.yAlign ||
      (function(t, e) {
        const { y: i, height: s } = e;
        return i < s / 2 ? 'top' : i > t.height - s / 2 ? 'bottom' : 'center';
      })(t, i);
    return { xAlign: i.xAlign || e.xAlign || La(t, e, i, s), yAlign: s };
  }
  function Ra(t, e, i, s) {
    const { caretSize: n, caretPadding: o, cornerRadius: a } = t;
    const { xAlign: r, yAlign: l } = i;
    const h = n + o;
    const { topLeft: c, topRight: d, bottomLeft: u, bottomRight: f } = wi(a);
    let g = (function(t, e) {
      let { x: i, width: s } = t;
      return (e === 'right' ? (i -= s) : e === 'center' && (i -= s / 2), i);
    })(e, r);
    const p = (function(t, e, i) {
      let { y: s, height: n } = t;
      return (
        e === 'top' ? (s += i) : (s -= e === 'bottom' ? n + i : n / 2),
        s
      );
    })(e, l, h);
    return (
      l === 'center' ?
        r === 'left' ?
          (g += h) :
          r === 'right' && (g -= h) :
        r === 'left' ?
          (g -= Math.max(c, u) + n) :
          r === 'right' && (g += Math.max(d, f) + n),
      { x: Z(g, 0, s.width - e.width), y: Z(p, 0, s.height - e.height) }
    );
  }
  function Ia(t, e, i) {
    const s = ki(i.padding);
    return e === 'center' ?
      t.x + t.width / 2 :
      e === 'right' ?
        t.x + t.width - s.right :
        t.x + s.left;
  }
  function za(t) {
    return Ca([], Oa(t));
  }
  function Fa(t, e) {
    const i =
      e && e.dataset && e.dataset.tooltip && e.dataset.tooltip.callbacks;
    return i ? t.override(i) : t;
  }
  const Va = {
    beforeTitle: e,
    title(t) {
      if (t.length > 0) {
        const e = t[0];
        const i = e.chart.data.labels;
        const s = i ? i.length : 0;
        if (this && this.options && this.options.mode === 'dataset') {
          return e.dataset.label || '';
        }
        if (e.label) {
          return e.label;
        }
        if (s > 0 && e.dataIndex < s) {
          return i[e.dataIndex];
        }
      }
      return '';
    },
    afterTitle: e,
    beforeBody: e,
    beforeLabel: e,
    label(t) {
      if (this && this.options && this.options.mode === 'dataset') {
        return `${t.label}: ${t.formattedValue}` || t.formattedValue;
      }
      let e = t.dataset.label || '';
      e && (e += ': ');
      const i = t.formattedValue;
      return (s(i) || (e += i), e);
    },
    labelColor(t) {
      const e = t.chart
        .getDatasetMeta(t.datasetIndex)
        .controller.getStyle(t.dataIndex);
      return {
        borderColor: e.borderColor,
        backgroundColor: e.backgroundColor,
        borderWidth: e.borderWidth,
        borderDash: e.borderDash,
        borderDashOffset: e.borderDashOffset,
        borderRadius: 0,
      };
    },
    labelTextColor() {
      return this.options.bodyColor;
    },
    labelPointStyle(t) {
      const e = t.chart
        .getDatasetMeta(t.datasetIndex)
        .controller.getStyle(t.dataIndex);
      return { pointStyle: e.pointStyle, rotation: e.rotation };
    },
    afterLabel: e,
    afterBody: e,
    beforeFooter: e,
    footer: e,
    afterFooter: e,
  };
  function Ba(t, e, i, s) {
    const n = t[e].call(i, s);
    return void 0 === n ? Va[e].call(i, s) : n;
  }
  class Wa extends $s {
    static positioners = Da;
    constructor(t) {
      (super(),
      (this.opacity = 0),
      (this._active = []),
      (this._eventPosition = void 0),
      (this._size = void 0),
      (this._cachedAnimations = void 0),
      (this._tooltipItems = []),
      (this.$animations = void 0),
      (this.$context = void 0),
      (this.chart = t.chart),
      (this.options = t.options),
      (this.dataPoints = void 0),
      (this.title = void 0),
      (this.beforeBody = void 0),
      (this.body = void 0),
      (this.afterBody = void 0),
      (this.footer = void 0),
      (this.xAlign = void 0),
      (this.yAlign = void 0),
      (this.x = void 0),
      (this.y = void 0),
      (this.height = void 0),
      (this.width = void 0),
      (this.caretX = void 0),
      (this.caretY = void 0),
      (this.labelColors = void 0),
      (this.labelPointStyles = void 0),
      (this.labelTextColors = void 0));
    }

    initialize(t) {
      ((this.options = t),
      (this._cachedAnimations = void 0),
      (this.$context = void 0));
    }

    _resolveAnimations() {
      const t = this._cachedAnimations;
      if (t) {
        return t;
      }
      const e = this.chart;
      const i = this.options.setContext(this.getContext());
      const s = i.enabled && e.options.animation && i.animations;
      const n = new Ts(this.chart, s);
      return (s._cacheable && (this._cachedAnimations = Object.freeze(n)), n);
    }

    getContext() {
      return (
        this.$context ||
        (this.$context =
          ((t = this.chart.getContext()),
          (e = this),
          (i = this._tooltipItems),
          Ci(t, { tooltip: e, tooltipItems: i, type: 'tooltip' })))
      );
    }

    getTitle(t, e) {
      const { callbacks: i } = e;
      const s = Ba(i, 'beforeTitle', this, t);
      const n = Ba(i, 'title', this, t);
      const o = Ba(i, 'afterTitle', this, t);
      let a = [];
      return ((a = Ca(a, Oa(s))), (a = Ca(a, Oa(n))), (a = Ca(a, Oa(o))), a);
    }

    getBeforeBody(t, e) {
      return za(Ba(e.callbacks, 'beforeBody', this, t));
    }

    getBody(t, e) {
      const { callbacks: i } = e;
      const s = [];
      return (
        u(t, t => {
          const e = { before: [], lines: [], after: [] };
          const n = Fa(i, t);
          (Ca(e.before, Oa(Ba(n, 'beforeLabel', this, t))),
          Ca(e.lines, Ba(n, 'label', this, t)),
          Ca(e.after, Oa(Ba(n, 'afterLabel', this, t))),
          s.push(e));
        }),
        s
      );
    }

    getAfterBody(t, e) {
      return za(Ba(e.callbacks, 'afterBody', this, t));
    }

    getFooter(t, e) {
      const { callbacks: i } = e;
      const s = Ba(i, 'beforeFooter', this, t);
      const n = Ba(i, 'footer', this, t);
      const o = Ba(i, 'afterFooter', this, t);
      let a = [];
      return ((a = Ca(a, Oa(s))), (a = Ca(a, Oa(n))), (a = Ca(a, Oa(o))), a);
    }

    _createItems(t) {
      const e = this._active;
      const i = this.chart.data;
      const s = [];
      const n = [];
      const o = [];
      let a;
      let r;
      let l = [];
      for (a = 0, r = e.length; a < r; ++a) {
        l.push(Aa(this.chart, e[a]));
      }
      return (
        t.filter && (l = l.filter((e, s, n) => t.filter(e, s, n, i))),
        t.itemSort && (l = l.sort((e, s) => t.itemSort(e, s, i))),
        u(l, e => {
          const i = Fa(t.callbacks, e);
          (s.push(Ba(i, 'labelColor', this, e)),
          n.push(Ba(i, 'labelPointStyle', this, e)),
          o.push(Ba(i, 'labelTextColor', this, e)));
        }),
        (this.labelColors = s),
        (this.labelPointStyles = n),
        (this.labelTextColors = o),
        (this.dataPoints = l),
        l
      );
    }

    update(t, e) {
      const i = this.options.setContext(this.getContext());
      const s = this._active;
      let n;
      let o = [];
      if (s.length) {
        const t = Da[i.position].call(this, s, this._eventPosition);
        ((o = this._createItems(i)),
        (this.title = this.getTitle(o, i)),
        (this.beforeBody = this.getBeforeBody(o, i)),
        (this.body = this.getBody(o, i)),
        (this.afterBody = this.getAfterBody(o, i)),
        (this.footer = this.getFooter(o, i)));
        const e = (this._size = Ta(this, i));
        const a = Object.assign({}, t, e);
        const r = Ea(this.chart, i, a);
        const l = Ra(i, a, r, this.chart);
        ((this.xAlign = r.xAlign),
        (this.yAlign = r.yAlign),
        (n = {
          opacity: 1,
          x: l.x,
          y: l.y,
          width: e.width,
          height: e.height,
          caretX: t.x,
          caretY: t.y,
        }));
      } else {
        this.opacity !== 0 && (n = { opacity: 0 });
      }
      ((this._tooltipItems = o),
      (this.$context = void 0),
      n && this._resolveAnimations().update(this, n),
      t &&
          i.external &&
          i.external.call(this, {
            chart: this.chart,
            tooltip: this,
            replay: e,
          }));
    }

    drawCaret(t, e, i, s) {
      const n = this.getCaretPosition(t, i, s);
      (e.lineTo(n.x1, n.y1), e.lineTo(n.x2, n.y2), e.lineTo(n.x3, n.y3));
    }

    getCaretPosition(t, e, i) {
      const { xAlign: s, yAlign: n } = this;
      const { caretSize: o, cornerRadius: a } = i;
      const { topLeft: r, topRight: l, bottomLeft: h, bottomRight: c } = wi(a);
      const { x: d, y: u } = t;
      const { width: f, height: g } = e;
      let p, m, x, b, _, y;
      return (
        n === 'center' ?
          ((_ = u + g / 2),
          s === 'left' ?
            ((p = d), (m = p - o), (b = _ + o), (y = _ - o)) :
            ((p = d + f), (m = p + o), (b = _ - o), (y = _ + o)),
          (x = p)) :
          ((m =
              s === 'left' ?
                d + Math.max(r, h) + o :
                s === 'right' ?
                  d + f - Math.max(l, c) - o :
                  this.caretX),
          n === 'top' ?
            ((b = u), (_ = b - o), (p = m - o), (x = m + o)) :
            ((b = u + g), (_ = b + o), (p = m + o), (x = m - o)),
          (y = b)),
        { x1: p, x2: m, x3: x, y1: b, y2: _, y3: y }
      );
    }

    drawTitle(t, e, i) {
      const s = this.title;
      const n = s.length;
      let o, a, r;
      if (n) {
        const l = Oi(i.rtl, this.x, this.width);
        for (
          t.x = Ia(this, i.titleAlign, i),
          e.textAlign = l.textAlign(i.titleAlign),
          e.textBaseline = 'middle',
          o = Si(i.titleFont),
          a = i.titleSpacing,
          e.fillStyle = i.titleColor,
          e.font = o.string,
          r = 0;
          r < n;
          ++r
        ) {
          (e.fillText(s[r], l.x(t.x), t.y + o.lineHeight / 2),
          (t.y += o.lineHeight + a),
          r + 1 === n && (t.y += i.titleMarginBottom - a));
        }
      }
    }

    _drawColorBox(t, e, i, s, n) {
      const a = this.labelColors[i];
      const r = this.labelPointStyles[i];
      const { boxHeight: l, boxWidth: h } = n;
      const c = Si(n.bodyFont);
      const d = Ia(this, 'left', n);
      const u = s.x(d);
      const f = l < c.lineHeight ? (c.lineHeight - l) / 2 : 0;
      const g = e.y + f;
      if (n.usePointStyle) {
        const e = {
          radius: Math.min(h, l) / 2,
          pointStyle: r.pointStyle,
          rotation: r.rotation,
          borderWidth: 1,
        };
        const i = s.leftForLtr(u, h) + h / 2;
        const o = g + l / 2;
        ((t.strokeStyle = n.multiKeyBackground),
        (t.fillStyle = n.multiKeyBackground),
        Le(t, e, i, o),
        (t.strokeStyle = a.borderColor),
        (t.fillStyle = a.backgroundColor),
        Le(t, e, i, o));
      } else {
        ((t.lineWidth = o(a.borderWidth) ?
          Math.max(...Object.values(a.borderWidth)) :
          a.borderWidth || 1),
        (t.strokeStyle = a.borderColor),
        t.setLineDash(a.borderDash || []),
        (t.lineDashOffset = a.borderDashOffset || 0));
        const e = s.leftForLtr(u, h);
        const i = s.leftForLtr(s.xPlus(u, 1), h - 2);
        const r = wi(a.borderRadius);
        Object.values(r).some(t => t !== 0) ?
          (t.beginPath(),
          (t.fillStyle = n.multiKeyBackground),
          He(t, { x: e, y: g, w: h, h: l, radius: r }),
          t.fill(),
          t.stroke(),
          (t.fillStyle = a.backgroundColor),
          t.beginPath(),
          He(t, { x: i, y: g + 1, w: h - 2, h: l - 2, radius: r }),
          t.fill()) :
          ((t.fillStyle = n.multiKeyBackground),
          t.fillRect(e, g, h, l),
          t.strokeRect(e, g, h, l),
          (t.fillStyle = a.backgroundColor),
          t.fillRect(i, g + 1, h - 2, l - 2));
      }
      t.fillStyle = this.labelTextColors[i];
    }

    drawBody(t, e, i) {
      const { body: s } = this;
      const {
        bodySpacing: n,
        bodyAlign: o,
        displayColors: a,
        boxHeight: r,
        boxWidth: l,
        boxPadding: h,
      } = i;
      const c = Si(i.bodyFont);
      let d = c.lineHeight;
      let f = 0;
      const g = Oi(i.rtl, this.x, this.width);
      const p = function(i) {
        (e.fillText(i, g.x(t.x + f), t.y + d / 2), (t.y += d + n));
      };
      const m = g.textAlign(o);
      let x, b, _, y, v, M, w;
      for (
        e.textAlign = o,
        e.textBaseline = 'middle',
        e.font = c.string,
        t.x = Ia(this, m, i),
        e.fillStyle = i.bodyColor,
        u(this.beforeBody, p),
        f = a && m !== 'right' ? (o === 'center' ? l / 2 + h : l + 2 + h) : 0,
        y = 0,
        M = s.length;
        y < M;
        ++y
      ) {
        for (
          x = s[y],
          b = this.labelTextColors[y],
          e.fillStyle = b,
          u(x.before, p),
          _ = x.lines,
          a &&
              _.length &&
              (this._drawColorBox(e, t, y, g, i),
              (d = Math.max(c.lineHeight, r))),
          v = 0,
          w = _.length;
          v < w;
          ++v
        ) {
          (p(_[v]), (d = c.lineHeight));
        }
        u(x.after, p);
      }
      ((f = 0), (d = c.lineHeight), u(this.afterBody, p), (t.y -= n));
    }

    drawFooter(t, e, i) {
      const s = this.footer;
      const n = s.length;
      let o, a;
      if (n) {
        const r = Oi(i.rtl, this.x, this.width);
        for (
          t.x = Ia(this, i.footerAlign, i),
          t.y += i.footerMarginTop,
          e.textAlign = r.textAlign(i.footerAlign),
          e.textBaseline = 'middle',
          o = Si(i.footerFont),
          e.fillStyle = i.footerColor,
          e.font = o.string,
          a = 0;
          a < n;
          ++a
        ) {
          (e.fillText(s[a], r.x(t.x), t.y + o.lineHeight / 2),
          (t.y += o.lineHeight + i.footerSpacing));
        }
      }
    }

    drawBackground(t, e, i, s) {
      const { xAlign: n, yAlign: o } = this;
      const { x: a, y: r } = t;
      const { width: l, height: h } = i;
      const {
        topLeft: c,
        topRight: d,
        bottomLeft: u,
        bottomRight: f,
      } = wi(s.cornerRadius);
      ((e.fillStyle = s.backgroundColor),
      (e.strokeStyle = s.borderColor),
      (e.lineWidth = s.borderWidth),
      e.beginPath(),
      e.moveTo(a + c, r),
      o === 'top' && this.drawCaret(t, e, i, s),
      e.lineTo(a + l - d, r),
      e.quadraticCurveTo(a + l, r, a + l, r + d),
      o === 'center' && n === 'right' && this.drawCaret(t, e, i, s),
      e.lineTo(a + l, r + h - f),
      e.quadraticCurveTo(a + l, r + h, a + l - f, r + h),
      o === 'bottom' && this.drawCaret(t, e, i, s),
      e.lineTo(a + u, r + h),
      e.quadraticCurveTo(a, r + h, a, r + h - u),
      o === 'center' && n === 'left' && this.drawCaret(t, e, i, s),
      e.lineTo(a, r + c),
      e.quadraticCurveTo(a, r, a + c, r),
      e.closePath(),
      e.fill(),
      s.borderWidth > 0 && e.stroke());
    }

    _updateAnimationTarget(t) {
      const e = this.chart;
      const i = this.$animations;
      const s = i && i.x;
      const n = i && i.y;
      if (s || n) {
        const i = Da[t.position].call(this, this._active, this._eventPosition);
        if (!i) {
          return;
        }
        const o = (this._size = Ta(this, t));
        const a = Object.assign({}, i, this._size);
        const r = Ea(e, t, a);
        const l = Ra(t, a, r, e);
        (s._to === l.x && n._to === l.y) ||
          ((this.xAlign = r.xAlign),
          (this.yAlign = r.yAlign),
          (this.width = o.width),
          (this.height = o.height),
          (this.caretX = i.x),
          (this.caretY = i.y),
          this._resolveAnimations().update(this, l));
      }
    }

    _willRender() {
      return !!this.opacity;
    }

    draw(t) {
      const e = this.options.setContext(this.getContext());
      let i = this.opacity;
      if (!i) {
        return;
      }
      this._updateAnimationTarget(e);
      const s = { width: this.width, height: this.height };
      const n = { x: this.x, y: this.y };
      i = Math.abs(i) < 0.001 ? 0 : i;
      const o = ki(e.padding);
      const a =
          this.title.length ||
          this.beforeBody.length ||
          this.body.length ||
          this.afterBody.length ||
          this.footer.length;
      e.enabled &&
        a &&
        (t.save(),
        (t.globalAlpha = i),
        this.drawBackground(n, t, s, e),
        Ai(t, e.textDirection),
        (n.y += o.top),
        this.drawTitle(n, t, e),
        this.drawBody(n, t, e),
        this.drawFooter(n, t, e),
        Ti(t, e.textDirection),
        t.restore());
    }

    getActiveElements() {
      return this._active || [];
    }

    setActiveElements(t, e) {
      const i = this._active;
      const s = t.map(({ datasetIndex: t, index: e }) => {
        const i = this.chart.getDatasetMeta(t);
        if (!i) {
          throw new Error(`Cannot find a dataset at index ${t}`);
        }
        return { datasetIndex: t, element: i.data[e], index: e };
      });
      const n = !f(i, s);
      const o = this._positionChanged(s, e);
      (n || o) &&
        ((this._active = s),
        (this._eventPosition = e),
        (this._ignoreReplayEvents = !0),
        this.update(!0));
    }

    handleEvent(t, e, i = !0) {
      if (e && this._ignoreReplayEvents) {
        return !1;
      }
      this._ignoreReplayEvents = !1;
      const s = this.options;
      const n = this._active || [];
      const o = this._getActiveElements(t, n, e, i);
      const a = this._positionChanged(o, t);
      const r = e || !f(o, n) || a;
      return (
        r &&
          ((this._active = o),
          (s.enabled || s.external) &&
            ((this._eventPosition = { x: t.x, y: t.y }), this.update(!0, e))),
        r
      );
    }

    _getActiveElements(t, e, i, s) {
      const n = this.options;
      if (t.type === 'mouseout') {
        return [];
      }
      if (!s) {
        return e.filter(
          t =>
            this.chart.data.datasets[t.datasetIndex] &&
            void 0 !==
              this.chart
                .getDatasetMeta(t.datasetIndex)
                .controller.getParsed(t.index),
        );
      }
      const o = this.chart.getElementsAtEventForMode(t, n.mode, n, i);
      return (n.reverse && o.reverse(), o);
    }

    _positionChanged(t, e) {
      const { caretX: i, caretY: s, options: n } = this;
      const o = Da[n.position].call(this, t, e);
      return !1 !== o && (i !== o.x || s !== o.y);
    }
  }
  var Na = {
    id: 'tooltip',
    _element: Wa,
    positioners: Da,
    afterInit(t, e, i) {
      i && (t.tooltip = new Wa({ chart: t, options: i }));
    },
    beforeUpdate(t, e, i) {
      t.tooltip && t.tooltip.initialize(i);
    },
    reset(t, e, i) {
      t.tooltip && t.tooltip.initialize(i);
    },
    afterDraw(t) {
      const e = t.tooltip;
      if (e && e._willRender()) {
        const i = { tooltip: e };
        if (
          !1 === t.notifyPlugins('beforeTooltipDraw', { ...i, cancelable: !0 })
        ) {
          return;
        }
        (e.draw(t.ctx), t.notifyPlugins('afterTooltipDraw', i));
      }
    },
    afterEvent(t, e) {
      if (t.tooltip) {
        const i = e.replay;
        t.tooltip.handleEvent(e.event, i, e.inChartArea) && (e.changed = !0);
      }
    },
    defaults: {
      enabled: !0,
      external: null,
      position: 'average',
      backgroundColor: 'rgba(0,0,0,0.8)',
      titleColor: '#fff',
      titleFont: { weight: 'bold' },
      titleSpacing: 2,
      titleMarginBottom: 6,
      titleAlign: 'left',
      bodyColor: '#fff',
      bodySpacing: 2,
      bodyFont: {},
      bodyAlign: 'left',
      footerColor: '#fff',
      footerSpacing: 2,
      footerMarginTop: 6,
      footerFont: { weight: 'bold' },
      footerAlign: 'left',
      padding: 6,
      caretPadding: 2,
      caretSize: 5,
      cornerRadius: 6,
      boxHeight: (t, e) => e.bodyFont.size,
      boxWidth: (t, e) => e.bodyFont.size,
      multiKeyBackground: '#fff',
      displayColors: !0,
      boxPadding: 0,
      borderColor: 'rgba(0,0,0,0)',
      borderWidth: 0,
      animation: { duration: 400, easing: 'easeOutQuart' },
      animations: {
        numbers: {
          type: 'number',
          properties: ['x', 'y', 'width', 'height', 'caretX', 'caretY'],
        },
        opacity: { easing: 'linear', duration: 200 },
      },
      callbacks: Va,
    },
    defaultRoutes: { bodyFont: 'font', footerFont: 'font', titleFont: 'font' },
    descriptors: {
      _scriptable: t =>
        t !== 'filter' && t !== 'itemSort' && t !== 'external',
      _indexable: !1,
      callbacks: { _scriptable: !1, _indexable: !1 },
      animation: { _fallback: !1 },
      animations: { _fallback: 'animation' },
    },
    additionalOptionScopes: ['interaction'],
  };
  return (
    Tn.register(Un, $o, go, t),
    (Tn.helpers = { ...Hi }),
    (Tn._adapters = In),
    (Tn.Animation = As),
    (Tn.Animations = Ts),
    (Tn.animator = bt),
    (Tn.controllers = nn.controllers.items),
    (Tn.DatasetController = js),
    (Tn.Element = $s),
    (Tn.elements = go),
    (Tn.Interaction = Ki),
    (Tn.layouts = ls),
    (Tn.platforms = Ds),
    (Tn.Scale = tn),
    (Tn.Ticks = ae),
    Object.assign(Tn, Un, $o, go, t, Ds),
    (Tn.Chart = Tn),
    typeof window !== 'undefined' && (window.Chart = Tn),
    Tn
  );
});
// # sourceMappingURL=chart.umd.min.js.map
