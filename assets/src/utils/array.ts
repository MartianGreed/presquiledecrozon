export function removeItemAtIndex<T>(idx: number, array: Array<T>) {
    return [
        ...array.slice(0, idx),
        ...array.slice(idx + 1),
    ];
}