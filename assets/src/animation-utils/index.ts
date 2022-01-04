function nextFrame(): Promise<void> {
    return new Promise(resolve => {
        requestAnimationFrame(() => {
            requestAnimationFrame(resolve as unknown as FrameRequestCallback);
        });
    });
}

async function afterTransition(element: HTMLElement): Promise<void> {
    return new Promise(resolve => {
        let duration = Number(
            getComputedStyle(element)
                .transitionDuration
                .replace('s', '')
        ) * 1000;

        setTimeout(() => {
            resolve();
        }, duration);
    });
}

export async function enter(el: HTMLElement, transitionName: string): Promise<void> {
    return await handleTransition({ el, type: 'enter', transitionName, removeBefore: true });
}

export async function leave(el: HTMLElement, transitionName: string): Promise<void> {
    return await handleTransition({ el, type: 'leave', transitionName, removeBefore: false });
}

type HandleTransitionType = {
    el: HTMLElement,
    type: string,
    transitionName: string,
    removeBefore: boolean,
    hiddenClass?: string,
};

async function handleTransition({ el, type, transitionName, removeBefore, hiddenClass = 'hidden' }: HandleTransitionType): Promise<void> {
    if (removeBefore) el.classList.remove(hiddenClass);

    el.classList.add(`${transitionName}-${type}`);
    el.classList.add(`${transitionName}-${type}-active`);

    await nextFrame();

    el.classList.remove(`${transitionName}-${type}`);

    await afterTransition(el);

    el.classList.remove(`${transitionName}-${type}-active`);

    if (!removeBefore) el.classList.add(hiddenClass);
}