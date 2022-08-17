export function fromDecimalToFloat(val: string): number {
    let parsed = val.replace(',', '.');
    return parseFloat(parsed);
}