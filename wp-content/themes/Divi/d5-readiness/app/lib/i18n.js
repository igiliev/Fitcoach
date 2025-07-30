// External dependencies.
import get from 'lodash/get';


const i18n = (context, [path, key], ...args) => {
  const newPath = [...path.split('.').filter(Boolean), ...key.split('.').filter(Boolean)];
  const value   = get(context, newPath, '');

  if (args.length > 0) {
    const sprintf = get(window, 'wp.i18n.sprintf');

    if (sprintf) {
      return sprintf(value, ...args);
    }

    return value.replace('%s', args[0]);
  }

  return value;
};


export const _n = (singular, plural, n, ...args) => {
  const [singularPath, singularKey] = singular;
  const [pluralPath, pluralKey] = plural;

  const singularNewPath = [...singularPath.split('.').filter(Boolean), ...singularKey.split('.').filter(Boolean)];
  const singularValue   = get(window.et_d5_readiness_data.i18n, singularNewPath, '');

  const pluralNewPath = [...pluralPath.split('.').filter(Boolean), ...pluralKey.split('.').filter(Boolean)];
  const pluralValue   = get(window.et_d5_readiness_data.i18n, pluralNewPath, '');

  const _n      = get(window, 'wp.i18n._n');
  const sprintf = get(window, 'wp.i18n.sprintf');

  if (args.length > 0) {
    return sprintf(_n(singularValue, pluralValue, n), ...args);
  }

  return sprintf(_n(singularValue, pluralValue, n));
}


export default (path, key, ...args) => i18n(window.et_d5_readiness_data.i18n, [path, key], ...args);
