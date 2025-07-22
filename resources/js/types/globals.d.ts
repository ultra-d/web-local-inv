import { AppPageProps } from '@/types/index';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps {}
}

declare module 'vue' {
    interface ComponentCustomProperties {
        $inertia: typeof Router;
        $page: Page;
        $headManager: ReturnType<typeof createHeadManager>;
    }
}

// ✨ TIPOS PARA NPROGRESS
declare global {
    interface Window {
        NProgress?: {
            configure: (options: {
                minimum?: number;
                easing?: string;
                speed?: number;
                trickle?: boolean;
                trickleSpeed?: number;
                template?: string;
            }) => void;
            start: () => void;
            done: () => void;
            set: (n: number) => void;
            inc: (amount?: number) => void;
        };
    }
}

// ✨ TIPOS PARA EVENTOS DE INERTIA
declare global {
    interface CustomEvent<T = any> {
        detail: T;
    }
}

// Tipos específicos para eventos de Inertia
interface InertiaStartEvent extends CustomEvent {
    detail: {
        visit: {
            url: {
                pathname: string;
            };
            method: string;
        };
    };
}

interface InertiaProgressEvent extends CustomEvent {
    detail: {
        progress?: {
            percentage: number;
        };
    };
}

interface InertiaFinishEvent extends CustomEvent {
    detail: {
        visit: {
            url: {
                pathname: string;
            };
        };
    };
}

interface InertiaErrorEvent extends CustomEvent {
    detail: {
        errors: any;
    };
}

interface InertiaBeforeEvent extends CustomEvent {
    detail: {
        visit: {
            method: string;
        };
    };
}

// Extender DocumentEventMap
declare global {
    interface DocumentEventMap {
        'inertia:start': InertiaStartEvent;
        'inertia:progress': InertiaProgressEvent;
        'inertia:finish': InertiaFinishEvent;
        'inertia:error': InertiaErrorEvent;
        'inertia:before': InertiaBeforeEvent;
    }
}

export {};
