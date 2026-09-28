const apiOrigin = (baseURL) => (baseURL || '').replace(/\/api\/?$/, '')

export function normalizeRoomCatalog(payload, baseURL) {
  const source = payload?.data ?? payload
  if (!Array.isArray(source)) return []

  return source.map((type) => {
    const availableUnit = type.kamar?.[0]
    const photoPath = type.foto?.[0]?.path || type.foto?.[0]?.url || ''
    const photo = !photoPath
      ? ''
      : /^https?:\/\//i.test(photoPath)
        ? photoPath
        : `${apiOrigin(baseURL)}/storage/${photoPath}`

    return {
      id: availableUnit?.id ?? type.id,
      typeId: type.id,
      name: type.nama || type.name || 'Tipe Kamar',
      desc: type.deskripsi || type.description || '',
      capacity: Number(type.kapasitas || type.capacity || 1),
      price: Number(type.harga_dasar || type.price || 0),
      status: availableUnit ? availableUnit.status : 'terisi',
      roomCount: Number(type.kamar_count || 0),
      availableCount: Number(type.kamar_tersedia_count || 0),
      photo,
    }
  })
}