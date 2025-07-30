# Framework

## What is Framework folder?
Framework folder is consisted of functionalities that are considered "Foundational" and are shared and used across namespaces.

For example `/Framework/REST/` is considered a foundational class because it offers the core functionality needed to create REST API endpoints in WP.  Please note that `/Framework/REST/` does not register any REST API endpoint itself but rather expose core functions to do it.

## How to use the Foundation functionalities?
Functionalities in `/Framework` folder are not to be initialized within themselves but rather be included and called from other classes. This means classes in `/Framework` are not included automatically and need to be called from folders such as `/server/VisualBuilder`.

