import { describe, expect, it } from 'vitest'
import { reactive } from 'vue'
import useSourceText, {
  normalizeSourceTextFormat,
  sourceTextAsString,
  sourceTextFirstLine,
  sourceTextLineCount,
} from '../useSourceText'

describe('sourceText helpers', () => {
  it('normalizes allowed formats and falls back to md', () => {
    expect(normalizeSourceTextFormat('PHP')).toBe('php')
    expect(normalizeSourceTextFormat('json')).toBe('md')
    expect(normalizeSourceTextFormat(undefined)).toBe('md')
  })

  it('treats null as empty string', () => {
    expect(sourceTextAsString(null)).toBe('')
    expect(sourceTextFirstLine(null)).toBe('')
    expect(sourceTextLineCount(null)).toBe(0)
  })

  it('uses the first line for preview and counts lines', () => {
    const body = '# Title\n\nParagraph'
    expect(sourceTextFirstLine(body)).toBe('# Title')
    expect(sourceTextLineCount(body)).toBe(3)
    expect(sourceTextLineCount('')).toBe(0)
    expect(sourceTextLineCount('one')).toBe(1)
    expect(sourceTextFirstLine('')).toBe('')
  })
})

describe('useSourceText', () => {
  it('Keep commits the draft and Cancel with no dirty closes', () => {
    const emitted = []
    const props = reactive({
      modelValue: 'hello',
      format: 'md',
    })
    const emit = (event, value) => emitted.push([event, value])
    const st = useSourceText(props, emit)

    st.openEditor()
    expect(st.dialogOpen.value).toBe(true)
    expect(st.draft.value).toBe('hello')

    st.draft.value = 'hello\nworld'
    expect(st.isDirty.value).toBe(true)
    st.keep()
    expect(emitted).toEqual([
      ['update:modelValue', 'hello\nworld'],
      ['change', 'hello\nworld'],
    ])
    expect(st.dialogOpen.value).toBe(false)
  })

  it('dirty close asks to discard; discard does not emit', () => {
    const emitted = []
    const props = reactive({ modelValue: 'a', format: 'txt' })
    const st = useSourceText(props, (event, value) => emitted.push([event, value]))

    st.openEditor()
    st.draft.value = 'b'
    st.requestClose()
    expect(st.confirmDiscard.value).toBe(true)
    expect(st.dialogOpen.value).toBe(true)

    st.discard()
    expect(emitted).toEqual([])
    expect(st.dialogOpen.value).toBe(false)
    expect(st.committed.value).toBe('a')
  })

  it('Keep may commit an empty string', () => {
    const emitted = []
    const props = reactive({ modelValue: 'x', format: 'md' })
    const st = useSourceText(props, (event, value) => emitted.push([event, value]))

    st.openEditor()
    st.draft.value = ''
    st.keep()
    expect(emitted).toEqual([
      ['update:modelValue', ''],
      ['change', ''],
    ])
  })

  it('opens on the edit pane and only md/html have preview', () => {
    const md = useSourceText(reactive({ modelValue: '', format: 'md' }), () => {})
    md.pane.value = 'preview'
    md.openEditor()
    expect(md.pane.value).toBe('edit')
    expect(md.hasPreview.value).toBe(true)

    const js = useSourceText(reactive({ modelValue: '', format: 'js' }), () => {})
    expect(js.hasPreview.value).toBe(false)
  })

  it('closedRules evaluate functions against committed text', () => {
    const props = reactive({
      modelValue: 'full\nbody',
      rules: [(v) => v.includes('full') || 'missing'],
    })
    const st = useSourceText(props, () => {})
    expect(st.firstLine.value).toBe('full')
    expect(st.closedRules.value[0]()).toBe(true)
  })
})
