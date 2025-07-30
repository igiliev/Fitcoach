import { state } from 'cerebral';

function insertCodeIntoField({ get, props: { snippet, codeMirrorId, isAppend, skipInsert, setEditorValue } }) {
  if (skipInsert) {
    return Promise.resolve();
  }

  if (codeMirrorId) {
    const codeMirrorInstance = jQuery(`#${codeMirrorId}`).next('.CodeMirror')[0].CodeMirror;

    // Insert code into specified codeMirror field.
    // Append or replace depending on user preferences.
    if (isAppend) {
      snippet = codeMirrorInstance.getValue() ? '\n' + snippet : snippet;
      return Promise.resolve(codeMirrorInstance.replaceRange(snippet, {line: codeMirrorInstance.lastLine()}));
    }

    return Promise.resolve(codeMirrorInstance.setValue(snippet));
  }

  if (isAppend) {
    snippet = get(state`content`) ? get(state`content`) + '\n' + snippet : snippet;
    setEditorValue(snippet);
  } else {
    setEditorValue(snippet);
  }

  return Promise.resolve();
}

export {
  insertCodeIntoField,
};
