// public/js/auto-collapse-sidebar.js
class AutoCollapseSidebar {
    constructor(options = {}) {
        this.config = {
            inactiveTime: options.delay || 5000,
            enabled: options.enabled !== false,
            hoverExpand: options.hoverExpand !== false,
            showTooltip: options.showTooltip !== false,
        };

        this.sidebarTimer = null;
        this.isAutoCollapsed = false;
        this.isHovering = false;
        this.sidebar = null;

        if (this.config.enabled) {
            this.init();
        }
    }

    init() {
        // Find sidebar element
        this.sidebar =
            document.querySelector("[data-sidebar]") ||
            document.querySelector("aside.fi-sidebar") ||
            document.querySelector('nav[class*="sidebar"]') ||
            document.querySelector('aside[class*="fi-"]');

        if (!this.sidebar) {
            console.warn("Auto-collapse: Sidebar element not found");
            return;
        }

        this.setupTooltips();
        this.attachEventListeners();
        this.setupPublicAPI();
        this.resetTimer();

        console.log(
            "Auto-collapse sidebar initialized with delay:",
            this.config.inactiveTime + "ms"
        );
    }

    setupTooltips() {
        if (!this.config.showTooltip) return;

        setTimeout(() => {
            const navItems = this.sidebar.querySelectorAll(
                ".fi-sidebar-nav-item"
            );
            navItems.forEach((item) => {
                const label = item.querySelector(".fi-sidebar-nav-item-label");
                if (label && !item.hasAttribute("title")) {
                    item.setAttribute("title", label.textContent.trim());
                }
            });
        }, 100);
    }

    collapseSidebar() {
        if (!this.isAutoCollapsed && !this.isHovering) {
            this.sidebar.classList.add("auto-collapsed");
            this.isAutoCollapsed = true;

            // Dispatch custom event
            window.dispatchEvent(
                new CustomEvent("sidebar-auto-collapsed", {
                    detail: { collapsed: true },
                })
            );
        }
    }

    expandSidebar() {
        if (this.isAutoCollapsed) {
            this.sidebar.classList.remove("auto-collapsed");
            this.isAutoCollapsed = false;

            // Dispatch custom event
            window.dispatchEvent(
                new CustomEvent("sidebar-auto-collapsed", {
                    detail: { collapsed: false },
                })
            );
        }
    }

    resetTimer() {
        clearTimeout(this.sidebarTimer);
        if (!this.isHovering) {
            this.sidebarTimer = setTimeout(
                () => this.collapseSidebar(),
                this.config.inactiveTime
            );
        }
    }

    startTimer() {
        clearTimeout(this.sidebarTimer);
        this.sidebarTimer = setTimeout(
            () => this.collapseSidebar(),
            this.config.inactiveTime
        );
    }

    attachEventListeners() {
        // Sidebar events
        this.sidebar.addEventListener("mouseenter", () => {
            this.isHovering = true;
            clearTimeout(this.sidebarTimer);
            if (this.config.hoverExpand) {
                this.expandSidebar();
            }
        });

        this.sidebar.addEventListener("mouseleave", () => {
            this.isHovering = false;
            this.startTimer();
        });

        this.sidebar.addEventListener("click", () => {
            this.expandSidebar();
            this.resetTimer();
        });

        this.sidebar.addEventListener(
            "focus",
            () => {
                this.expandSidebar();
                this.resetTimer();
            },
            true
        );

        // Main content events
        const mainContent =
            document.querySelector("main") ||
            document.querySelector(".fi-main") ||
            document.querySelector('[class*="fi-main"]') ||
            document.body;

        const events = ["click", "keydown", "scroll", "touchstart"];
        events.forEach((event) => {
            mainContent.addEventListener(event, () => this.resetTimer());
        });

        // Window events
        window.addEventListener("resize", () => {
            if (window.innerWidth <= 1024) {
                this.expandSidebar();
                clearTimeout(this.sidebarTimer);
            } else {
                this.resetTimer();
            }
        });

        // Handle route changes
        this.handleRouteChanges();
    }

    handleRouteChanges() {
        let currentPath = window.location.pathname;
        const observer = new MutationObserver(() => {
            if (window.location.pathname !== currentPath) {
                currentPath = window.location.pathname;
                this.expandSidebar();
                this.resetTimer();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    setupPublicAPI() {
        window.autoCollapseSidebar = {
            collapse: () => this.collapseSidebar(),
            expand: () => this.expandSidebar(),
            toggle: () => {
                if (this.isAutoCollapsed) {
                    this.expandSidebar();
                } else {
                    this.collapseSidebar();
                }
            },
            isCollapsed: () => this.isAutoCollapsed,
            resetTimer: () => this.resetTimer(),
        };
    }
}

// Auto initialize when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
    // Get config from window object (set by PHP)
    const config = window.autoCollapseConfig || {};
    new AutoCollapseSidebar(config);
});
