export const noop = () => {}

export const isNull = <T extends NonNullable<unknown>>(value: T | null): value is null => {
  return value === null
}
