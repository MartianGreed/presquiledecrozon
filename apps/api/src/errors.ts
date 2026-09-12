export class Problem extends Error {
  constructor(
    readonly status: number,
    readonly code: string,
    message: string,
  ) {
    super(message);
  }
}
export function assert(
  condition: unknown,
  status: number,
  code: string,
  message: string,
): asserts condition {
  if (!condition) throw new Problem(status, code, message);
}
export const invalid = (message: string) =>
  new Problem(400, "validation", message);
