import mermaid from 'mermaid';

function initMermaid() {
    const nodes = document.querySelectorAll('pre.mermaid');
    if (nodes.length === 0) {
        return;
    }

    mermaid.initialize({
        startOnLoad: false,
        securityLevel: 'strict',
    });

    void mermaid.run({ nodes: [...nodes] });
}

document.addEventListener('DOMContentLoaded', initMermaid);
