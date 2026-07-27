# Architecture

The bootstrap defines plugin constants, loads Composer, and starts the plugin
coordinator. The coordinator receives modules through the `wpht_modules`
filter, so a new module can be added without changing existing modules.

```mermaid
flowchart LR
    B[Plugin bootstrap] --> P[Plugin coordinator]
    P --> M[Module contract]
    M --> L[Logging]
    M --> R[REST hardening]
    M --> X[XML-RPC hardening]
    M --> U[User enumeration]
    M --> W[WordPress hardening]
    P --> A[Admin and settings]
```

All behavior-changing modules are opt-in. SQL access for security events is
confined to the logging subsystem.
