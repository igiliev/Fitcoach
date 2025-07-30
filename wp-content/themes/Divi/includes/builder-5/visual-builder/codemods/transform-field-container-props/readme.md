### FieldContainer Props Codemod
This codemod helps automate the transformation of three specific properties (`sticky`, `responsive`, `hover`) in the FieldContainer component to be grouped under a single `features` prop.

### Motivation
We noticed that having multiple feature props in the FieldContainer component was making the component's interface cluttered. With the introduction of Dynamic Content feature, Grouping these props into a single `features`` prop makes the API cleaner and improves readability. This codemod is intended to be used on our large codebase where manual transformation could be cumbersome.

### How it works
Given a component that looks like:

`<FieldContainer sticky={true} responsive={false} hover={true} />`

The codemod will transform it into:

`<FieldContainer features={{ sticky: true, responsive: false, hover: true }} />`

If a features prop already exists on the component, the codemod will merge the transformed props into the existing features prop.

### Usage
Run the codemod on your codebase:

- `cd /visual-builder`
- `yarn jscodeshift -t codemods/transform-field-container-props/transformFieldContainerProps.js --extensions=tsx ./packages/module-library/`

**Note:** The codemod is intended to to be on `module-library` package. Since our styling rules have a bit of conflict with jscodeshift internal styling rules
running it on all files might introduce some unwanted code changes style wise.

For example we have a styling rule that return should have parentheses, running this codemod might introduce extra parentheses in some files. I didn't have time
to debug this so just leaving this info here for future references.

### Tests
Run the tests for this codemod:

- `cd /visual-builder`
- `yarn test-unit ./codemods/`