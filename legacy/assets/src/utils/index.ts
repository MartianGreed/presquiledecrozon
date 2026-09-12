export * from './array';
export * from './from-decimal-to-float';

export async  function sleep(duration: number): Promise<void> {
    return new Promise(resolve => {
        setTimeout(() => {
            resolve();
        }, duration);
    });
}